<?php

namespace App\Http\Controllers\markets;


use App\Models\Domains;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\TenantTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtp;
use App\Http\Requests\Auth\VendorLogin;
use App\Http\Requests\Auth\VerifyOtp;
use App\Http\Requests\Register\EditPhone;
use App\Http\Resources\UserResource;
use App\Models\MarketDomains;
use App\Models\Markets;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Traits\OtpServiceTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthMarketController extends Controller {

    use OtpServiceTrait, ResponseTrait, TenantTrait;
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'sendOtp', 'verifyOtp', 'get_sub_domain']]);
    }

    public function login(VendorLogin $request) {
        $inputs = $request->validated();
        $phone = $inputs['phone'];
        $country_id = $inputs['country_id'];

        $user = User::where('phone', $phone)->where('country_id', $country_id)->first();

        if (empty($user) || !Hash::check($inputs['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => __('auth.failed'),
            ], 401);
        }

        $data = [
            'user' => $user,
            'authorization' => $this->generateToken($user->phone, $user->country_id),
        ];

        $market = Markets::where('user_id', $user->id)->first();

        if (!empty($market) && $market->status == 1 && $user->status == 1) {
            $lang = app()->getLocale() === 'ar' ? 'ar' : 'en';
            $frontendDomain = str_replace(".api", "", $this->getDomain($user->phone, $user->country_id));
            $authorization = $this->getTenantToken($user->phone, $user->country_id);
            $data['domain'] = $frontendDomain . "?access_token=" . $authorization['token'] . "&lang=$lang";
        }

        return $this->getSuccessResponse('Logged in successfully', $data);
    }

    public function reset_password(Request $request) {
        $request->validate([
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|min:6|same:password',
        ]);
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();
        return $this->getSuccessResponse('Password reset successfully');
    }
    public function get_sub_domain(Request $request) {
        $request->validate([
            'sub_domain' => 'required|string',
        ]);
        $sub_domain = str_replace('.octopii.cloud', '', $request->sub_domain);
        $domain = Domains::where('domain', 'LIKE', "{$sub_domain}")->first();
        if (!$domain) {
            return $this->getErrorResponse('Domain not found', null, 404);
        }

        if (empty($domain->market)) {
            return $this->getErrorResponse('Marketplace not found', null, 404);
        }

        $subscription = $domain->market?->lastSubscription();
        // $expired = !empty($subscription?->expired_at) && Carbon::parse($subscription->expired_at)->isPast();
        // $canceled = !empty($subscription?->canceled_at) && Carbon::parse($subscription->canceled_at)->isPast();
        // if ($expired && !empty($subscription->grace_days_ended_at)) {
        //     $expired = Carbon::parse($subscription->grace_days_ended_at)->isPast();
        // }
        // if ($expired || $canceled) {
        //     return $this->getErrorResponse("expired_or_canceled_subscription", null, 400);
        // }

        if (!empty($subscription)) {
            $subscription->load(['plan.features']);
        }

        return $this->getSuccessResponse('success', [
            'url' => $domain->domain . '.' . env('MAIN_URL') . (env('APP_ENV') === 'local' ? ':8000' : '') . '/api/mk/v1',
            'subscription' => $subscription,
        ]);
    }

    public function editPhone(EditPhone $request) {
        $inputs = $request->validated();
        $user = $request->user();
        $user->update(['phone' => $inputs['phone']]);
        if ($this->SendOtpSms($user->phone, $user->country_id, 6)) {
            return response()->json([
                'status' => true,
                'message' => 'Otp sent successfully',
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Error! Please try again later',
        ], 503);
    }

    public function sendOtp(SendOtp $request) {
        $inputs = $request->validated();
        $phone = $inputs['phone'];
        $country_id = $inputs['country_id'];
        if ($this->SendOtpSms($phone, $country_id, 6)) {
            return response()->json([
                'status' => true,
                'message' => 'Otp sent successfully',
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Error! Please try again later',
        ], 503);
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
                'message' => 'otp not valid',
            ]);
        }
        $user = User::where('phone', $phone)->where('country_id', $country_id)->first();
        if (empty($user)) {
            return $this->getErrorResponse(__('no_user'));
        }

        $data = [
            'status' => true,
            'message' => 'success',
            'user' => $user,
            'authorization' => $this->generateToken($user->phone, $user->country_id),
        ];

        $user->status = 1;
        $user->save();

        $market = Markets::where('user_id', $user->id)->first();
        if ($market) {
            $frontendDomain = str_replace(".api", "", $this->getDomain($phone, $country_id));
            $market->status = 1;
            $market->save();
            Artisan::call('app:seed-tenant-market', [
                'user_id' => $user->id,
                'market_id' => $market->id,
            ]);
            $authorization = $this->getTenantToken($user->phone, $user->country_id);
            $lang = app()->getLocale() === 'ar' ? 'ar' : 'en';
            $data['domain'] = $frontendDomain . "?access_token=" . $authorization['token'] . "&lang=$lang";
        }

        return response()->json($data);
    }

    protected function getUser($phone, $country_id) {
        $user = User::where('phone', $phone)->where('country_id', $country_id)->first();
        if ($user) {
            return $user;
        }
        return null;
    }

    protected function generateToken($phone, $country_id) {
        $user = User::where('phone', $phone)->first();
        if (!$user || $country_id != $user->country_id) {
            return response()->json([
                'status' => false,
                'message' => __('auth.failed'),
            ], 401);
        }
        $token = $user->createToken('API Token')->accessToken;
        return [
            'token' => $token,
            'type' => 'Bearer',
        ];
    }


    protected function getTenantToken($phone, $country_id) {
        $user = User::where('phone', $phone)->first();
        if (!$user || $country_id != $user->country_id) {
            return response()->json([
                'status' => false,
                'message' => __('auth.failed'),
            ], 401);
        }
        $market_id = Markets::where('user_id', $user->id)->first()->id;

        $tenant = Tenant::wherehas('domains', function ($q) use ($market_id) {
            $q->where('market_id', $market_id);
        })->first();

        return $tenant->run(function () use ($phone) {
            $user = User::where('phone', $phone)->first();
            return [
                'token' => !empty($user) ? $user->createToken('API Token')->accessToken : '',
                'type' => 'Bearer',
            ];
        });
    }

    protected function getDomain($phone, $country_id) {
        $user = User::where('phone', $phone)->where('country_id', $country_id)->first();
        if ($user) {
            $market = Markets::where('user_id', $user->id)->first();
            if ($market) {
                $domain = Domains::where('market_id', $market->id)->first();
                if ($domain) {
                    return $domain->domain . "." . env('MAIN_URL');
                }
            }
        }
        return null;
    }
}
