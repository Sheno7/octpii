<?php

namespace App\Http\Controllers\vendors;


use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderApp\Login;
use App\Http\Requests\ProviderApp\RequestOtp;
use App\Http\Requests\ProviderApp\ResetPassword;
use App\Http\Requests\ProviderApp\VerifyOtp;
use App\Http\Resources\Provider\UserResource;
use App\Models\FcmToken;
use App\Traits\OtpServiceTrait;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthProviderController extends Controller {
    use OtpServiceTrait, ResponseTrait;
    public function __construct() {
        $this->middleware('auth:api')->except([
            'login',
            'register',
            'requestOtp',
            'verifyOtp',
        ]);
    }

    public function login(Login $request) {
        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password) || $request->country_id != $user->country_id) {
            return response()->json([
                'status' => false,
                'message' => __('auth.failed'),
            ], 401);
        }
        $token = $user->createToken('API Token')->accessToken;
        if ($request->has('fcm_token')) {
            $fcmToken = $request->fcm_token;
            try {
                FcmToken::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'fcm_token' => $fcmToken,
                    ],
                    [
                        'device_type' => $request->device_type,
                    ]
                );
            } catch (\Exception $e) {
                Log::error("Error saving FCM token for user_id: {$user->id}, token: {$fcmToken}. Exception: {$e->getMessage()}");
            }
        }
        return response()->json([
            'user' => $user,
            'vendor_name' => Setting::where('key', 'vendor_name')->first()->value ?? null,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer',
            ]
        ]);
    }

    public function profile() {
        $user = new UserResource(Auth::user());
        return $this->getSuccessResponse('Profile Retrieved Successfully', $user);
    }

    public function logout(Request $request) {
        $user = $request->user();
        $user->token()->revoke();

        if ($request->has('fcm_token')) {
            $fcmToken = $request->input('fcm_token');

            try {
                $token = FcmToken::where('user_id', $user->id)
                    ->where('fcm_token', $fcmToken)
                    ->first();

                if ($token) {
                    $token->delete();
                    Log::info("FCM token deleted successfully for user_id: {$user->id}");
                } else {
                    Log::warning("FCM token not found for user_id: {$user->id}, token: {$fcmToken}");
                }
            } catch (\Exception $e) {
                Log::error("Error deleting FCM token for user_id: {$user->id}, token: {$fcmToken}. Exception: {$e->getMessage()}");
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
        ]);
    }

    public function requestOtp(RequestOtp $request) {
        $inputs = $request->validated();
        $phone = $inputs['phone'];
        $country_id = $inputs['country_id'];
        $user = User::where('phone', $phone)->where('country_id', $country_id)->first();
        if (empty($user)) {
            return $this->getErrorResponse('No User Found', null, 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $this->SendOtpSms($phone, $country_id)
        ]);
    }

    public function verifyOtp(VerifyOtp $request) {
        $inputs = $request->validated();
        $phone = $inputs['phone'];
        $country_id = $inputs['country_id'];
        $otp = $inputs['otp'];
        $check = $this->verify($phone, $country_id, $otp);
        if (!$check) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP',
            ]);
        }

        $user = User::where('phone', $phone)->where('country_id', $country_id)->first();
        if (!empty($user)) {
            $user->status = 1;
            $user->save();
            $token = $user->createToken('API Token')->accessToken;
            return response()->json([
                'user' => $user,
                'vendor_name' => Setting::where('key', 'vendor_name')->first()->value ?? null,
                'authorization' => [
                    'token' => $token,
                    'type' => 'Bearer',
                ]
            ]);
        }
        return $this->getErrorResponse('No User Found', null, 404);
    }

    public function resetPassword(ResetPassword $request) {
        $inputs = $request->validated();
        $user = $request->user();
        $user->password = Hash::make($inputs['password']);
        $user->save();
        return $this->getSuccessResponse('Password Changed Successfully', $user);
    }
}
