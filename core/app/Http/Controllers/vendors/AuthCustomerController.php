<?php

namespace App\Http\Controllers\vendors;


use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\Login;
use App\Http\Requests\Customers\Register;
use App\Http\Requests\Customers\RequestOtp;
use App\Http\Requests\Customers\ResetPassword;
use App\Http\Requests\Customers\VerifyOtp;
use App\Http\Resources\Customer\UserResource;
use App\Models\AdditionalInformation;
use App\Models\Customers;
use App\Traits\OtpServiceTrait;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AuthCustomerController extends Controller {
    use OtpServiceTrait, ResponseTrait;
    public function __construct() {
        $this->middleware('auth:api')->except([
            'login',
            'register',
            'requestOtp',
            'verifyOtp',
        ]);
    }

    public function register(Register $request) {
        $inputs = $request->validated();

        try {
            DB::beginTransaction();
            $user = User::create([
                'first_name' => $inputs['first_name'],
                'last_name' => $inputs['last_name'],
                'name' => $inputs['first_name'] . ' ' . $inputs['last_name'],
                'phone' => $inputs['phone'],
                'country_id' => $inputs['country_id'],
                'password' => Hash::make($inputs['password'])
            ]);
            $customer = Customers::create([
                'user_id' => $user->id,
                'rating' => 0,
                'status' => 1,
            ]);
            $token = $user->createToken('API Token')->accessToken;
            $setting = Setting::where('key', 'customer_add')->value('value');
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
            }
            DB::commit();
            $this->SendOtpSms($user->phone, $user->country_id);
            return response()->json([
                'user' => $user,
                'vendor_name' => Setting::where('key', 'vendor_name')->first()->value ?? '',
                'authorization' => [
                    'token' => $token,
                    'type' => 'Bearer',
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
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
        $request->user()->token()->revoke();

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
    }

    public function resetPassword(ResetPassword $request) {
        $inputs = $request->validated();
        $user = $request->user();
        $user->password = Hash::make($inputs['password']);
        $user->save();
        return $this->getSuccessResponse('Password Changed Successfully', $user);
    }

    public function deleteAccount() {
        try {
            $user = Auth::user();
            User::findOrFail($user->id)->delete();
            return $this->getSuccessResponse("We're sad to see you go. Your account has been deleted. Reach out for help. Thanks.");
        } catch (\Throwable $th) {
            return $this->getErrorResponse('Something went wrong! Please try again later.');
        }
    }
}
