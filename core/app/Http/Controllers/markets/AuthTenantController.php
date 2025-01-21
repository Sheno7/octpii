<?php

namespace App\Http\Controllers\markets;


use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\RequestOtp;
use App\Http\Requests\Customers\VerifyOtp;
use App\Http\Requests\TenantCompleteRegistrationMarketRequest;
use App\Http\Requests\TenantRegistrationRequest;
use App\Http\Resources\UserResource;
use App\Traits\OtpServiceTrait;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthTenantController extends Controller {
    use OtpServiceTrait, ResponseTrait;
    public function __construct() {
        $this->middleware('auth:api')->except([
            'login',
            'register',
            'generate_token',
            'requestOtp',
            'verifyOtp',
        ]);
    }

    public function register(TenantRegistrationRequest $request) {
        $inputs = $request->validated();
        try {
            DB::beginTransaction();
            $password = Str::password(16);
            $inputs['user']['password'] = Hash::make($password);
            $inputs['user']['name'] = $inputs['user']['first_name'] . ' ' . $inputs['user']['last_name'];
            $user = User::create($inputs['user']);

            $user->utm_source = $request->get('utm_source', null);
            $user->utm_medium = $request->get('utm_medium', null);
            $user->utm_campaign = $request->get('utm_campaign', null);
            $user->utm_term = $request->get('utm_term', null);
            $user->utm_content = $request->get('utm_content', null);
            $user->save();

            DB::commit();
            return $this->getSuccessResponse('success', [
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }

    public function completeRegistration(TenantCompleteRegistrationMarketRequest $request) {
        $inputs = $request->validated();
        try {
            DB::beginTransaction();
            $user = $request->user();
            $market = $user->market()->create($inputs['market']);
            $market->status = 1;
            $market->save();
            $user->status = 1;
            $user->save();
            $market->sectors()->sync($inputs['market']['sectors']);
            DB::commit();
            config(['tenancy.migration_parameters.--path' => [database_path('migrations/_market')]]);
            $tenant = Tenant::create([
                'name' => $request->input('domain.domain'),
                'user_id' => $user->id,
                'market_id' => $market->id,
                'tenancy_db_name' => $market->id . ".api",
                'status' => 1
            ]);
            $tenant->domains()->create([
                'domain' => $request->input('domain.domain'),
                'tenant_id' => $tenant->id . ".api",
                'market_id' => $market->id
            ]);
            Artisan::call('app:seed-tenant-market', [
                'user_id' => $user->id,
                'market_id' => $market->id,
            ]);

            $frontendDomain = str_replace(".api", "", $inputs['domain']['domain'] . "." . env('MAIN_URL'));
            $phone = $user->phone;
            $authorization =  $tenant->run(function () use ($phone) {
                $user = User::where('phone', $phone)->first();
                return [
                    'token' => !empty($user) ? $user->createToken('API Token')->accessToken : '',
                    'type' => 'Bearer',
                ];
            });
            $lang = app()->getLocale() === 'ar' ? 'ar' : 'en';
            $domain = $frontendDomain . "?access_token=" . $authorization['token'] . "&lang=$lang";

            $token = $user->createToken('token');
            return $this->getSuccessResponse('success', [
                'user' => $user,
                'authorization' => [
                    'token' => $token->accessToken,
                    'type' => 'Bearer',
                ],
                'domain' => $domain,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }

    public function login(Request $request) {
        $request->validate([
            'phone' => ['required', 'min:11', 'max:11', 'regex:/^0[0-9]{10}$/'],
            'country_id' => 'required|integer|exists:countries,id',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (
            !$user
            || !Hash::check($request->password, $user->password)
            || $request->country_id != $user->country_id
            || !empty($user->customer)
        ) {
            return response()->json([
                'status' => false,
                'message' => __('auth.failed'),
            ], 401);
        }
        $token = $user->createToken('API Token')->accessToken;
        return response()->json([
            'user' => $user,
            'market_name' => Setting::where('key', 'market_name')->first()->value ?? null,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer',
            ]
        ]);
    }

    public function profile() {
        $user = User::find(Auth::user()->id);
        $token = $user->createToken('API Token')->accessToken;
        return response()->json([
            'user' => $user,
            'market_name' => Setting::where('key', 'market_name')->first()->value ?? null,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer',
            ]
        ]);
    }

    public function logout(Request $request) {
        $request->user()->token()->revoke();

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
        ]);
    }

    public function generate_token(Request $request) {
        $request->validate([
            'phone' => ['required', 'min:11', 'max:11', 'regex:/^0[0-9]{10}$/'],
            'country_id' => 'required|integer|exists:countries,id',
            'password' => 'required|string',
        ]);
        $user = User::where('phone', $request->phone)->first();
        if (!$user || $request->country_id != $user->country_id) {
            return response()->json([
                'status' => false,
                'message' => __('auth.failed'),
            ], 401);
        }
        $token = $user->createToken('API Token')->accessToken;
        return response()->json([
            'user' => $user,
            'token' => $token,
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

        $user = User::where('phone', $phone)->where('country_id', $country_id)->first();
        if (empty($user)) {
            return $this->getErrorResponse('No User Found', null, 404);
        }

        $user->status = 1;
        $user->save();
        $token = $user->createToken('API Token')->accessToken;
        return response()->json([
            'status' => true,
            'message' => 'success',
            'user' => $user,
            'market_name' => Setting::where('key', 'market_name')->first()->value ?? null,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer',
            ]
        ]);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6|same:password',
        ]);
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->getSuccessResponse('Password reset successfully');
    }
}
