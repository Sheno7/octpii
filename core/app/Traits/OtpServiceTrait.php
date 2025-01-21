<?php

namespace App\Traits;

use App\Models\Countries;
use App\Models\Otp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait OtpServiceTrait {

    public static function generateOtp($length = 4) {
        $chars = '0123456789';
        $count = mb_strlen($chars);
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= $chars[mt_rand(0, $count - 1)];
        }
        return $otp;
    }

    public function sendOtpSms($phone, $country_id,$length=4) {
        $otpCode = $this->generateOtp($length);
        $existingOtp = Otp::where('phone', $phone)->where('country_id', $country_id)->first();
        if (!$existingOtp) {
            $otp = new Otp();
            $otp->otp = $otpCode;
            $otp->phone = $phone;
            $otp->country_id = $country_id;
            $otp->save();
        } else {
            $existingOtp->delete();
            $existingOtp->otp = $otpCode;
            $existingOtp->save();
        }

        $url = 'https://smsvas.vlserv.com/VLSMSPlatformResellerAPI/NewSendingAPI/api/SMSSender/SendSMS';

        $parameters = [
            "UserName" => env('SMS_VL_USERNAME'),
            "Password" => env('SMS_VL_PASSWORD'),
            "SMSSender" => env('SMS_VL_SENDER'),
            "SMSText" => "$otpCode is your verification code for octopii.cloud",
            "SMSLang" => app()->getLocale(),
            "SMSReceiver" => "2$phone",
            "SMSID" => Str::uuid()->toString(),
        ];

        $code = '';
        if (app()->isProduction()) {
            $response = Http::post($url, $parameters);
            $json = json_decode($response->body(), true);
            $code = $json;
        } else {
            $code = 0;
        }

        return $code == 0;
    }

    public function verify($phone, $country_id, $otp) {
        if (!app()->isProduction() && ( $otp == '1234' || $otp == '123456')) {
            $this->removeOtp($phone, $country_id);
            return true;
        }
        $existingOtp = Otp::where('otp', $otp)->where('phone', $phone)->where('country_id', $country_id)->first();
        if ($existingOtp) {
            $this->removeOtpByPhone($phone, $country_id, $otp);
            return true;
        }
        return false;
    }

    public function removeOtp($phone, $country_id) {
        $existingOtp = Otp::where('phone', $phone)
            ->where('country_id', $country_id)->first();
        if ($existingOtp) {
            $existingOtp->delete();
        }
    }

    public function removeOtpByPhone($phone, $country_id, $otp) {
        $existingOtp = Otp::where('otp', $otp)
            ->where('phone', $phone)
            ->where('country_id', $country_id)
            ->first();
        if ($existingOtp) {
            $existingOtp->delete();
        }
    }
}
