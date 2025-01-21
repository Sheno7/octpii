<?php

namespace App\Http\Controllers\markets;


use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Market\Customers\Register;
use App\Http\Requests\Market\Customers\RequestOtp;
use App\Http\Requests\Market\Customers\VerifyOtp;
use App\Http\Resources\Customer\UserResource;
use App\Models\Customers;
use App\Models\FcmToken;
use App\Traits\OtpServiceTrait;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AuthCustomerController extends Controller {
    use OtpServiceTrait, ResponseTrait;
    public function __construct() {
        $this->middleware('auth:api')->except([
            'requestOtp',
            'verifyOtp',
        ]);
    }

    public function requestOtp(RequestOtp $request) {
        $inputs = $request->validated();
        $phone = $inputs['phone'];
        $country_id = $inputs['country_id'];
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $this->SendOtpSms($phone, $country_id, 6)
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

        $user = User::firstOrCreate([
            'phone' => $phone,
            'country_id' => $country_id,
        ], [
            'first_name' => 'DEFAULT',
            'last_name' => 'DEFAULT',
            'name' => 'DEFAULT',
            'password' => 'no-password',
        ]);

        Customers::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'rating' => 0,
            'status' => 1,
        ]);

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

        $token = $user->createToken('API Token')->accessToken;
        return response()->json([
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer',
            ]
        ]);
    }

    public function register(Register $request) {
        $inputs = $request->validated();
        try {
            $user = Auth::user();
            $user->update([
                'first_name' => $inputs['first_name'],
                'last_name' => $inputs['last_name'],
                'name' => $inputs['first_name'] . ' ' . $inputs['last_name'],
                'email' => $inputs['email'],
                'status' => 1
            ]);
            if (empty($user->customer)) {
                Customers::create([
                    'user_id' => $user->id,
                    'rating' => 0,
                    'status' => 1,
                ]);
            }
            /* $setting = Setting::where('key', 'customer_add')->value('value');
            if ($setting) {
                foreach ($setting as $item) {
                    $tmp = collect($request->get('additional_info', []))->where('type', $item['type'])->first();
                    if (empty($tmp)) {
                        $tmp = $item;
                    }
                    $additionalInfo = new AdditionalInformation();
                    $additionalInfo->customer_id = $customer->id;
                    $additionalInfo->type = $item['type'];
                    $additionalInfo->hasfile = $item['hasfile'];
                    $additionalInfo->value = $tmp;
                    $additionalInfo->save();
                }
            } */
            return response()->json([
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }

    public function profile() {
        $user = new UserResource(Auth::user());
        return $this->getSuccessResponse('Profile Retrieved Successfully', $user);
    }

    public function updateProfile(Register $request) {
        $inputs = $request->validated();
        try {
            $user = Auth::user();
            $user->update([
                'first_name' => $inputs['first_name'],
                'last_name' => $inputs['last_name'],
                'name' => $inputs['first_name'] . ' ' . $inputs['last_name'],
                'email' => $request->get('email'),
                'status' => 1
            ]);

            return $this->getSuccessResponse('Profile Updated Successfully', $user);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
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

    public function deleteAccount() {
        try {
            $user = request()->user();
            $user->token()->revoke();
            $user->delete();
            FcmToken::where('user_id', $user->id)->delete();
            return $this->getSuccessResponse("We're sad to see you go. Your account has been deleted. Reach out for help. Thanks.");
        } catch (\Throwable $th) {
            return $this->getErrorResponse('Something went wrong! Please try again later.');
        }
    }
}
