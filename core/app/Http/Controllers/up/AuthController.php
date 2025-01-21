<?php

namespace App\Http\Controllers\up;


use App\Models\User;
use App\Models\Vendors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\OtpServiceTrait;
use App\Traits\ResponseTrait;


class AuthController extends Controller
{

    use OtpServiceTrait, ResponseTrait;

//    public function register(Request $request): JsonResponse
//    {
//        $validator = Validator::make($request->all(), [
//            'name' => 'required',
//            'email' => 'required|email',
//            'password' => 'required',
//        ]);
//        if($validator->fails()){
//            return $this->sendError('Validation Error.', $validator->errors());
//        }
//        $input = $request->all();
//        $input['password'] = bcrypt($input['password']);
//        $user = User::create($input);
//        $success['token'] =  $user->createToken('MyApp')->accessToken;
//        return $this->getSuccessResponse($success);
//    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->getErrorResponse('Unauthorized', ['error' => 'Unauthorized']);
        }

        $vendor_name = Vendors::join('users', 'users.id', '=', 'vendors.user_id')
            ->where('users.phone', $request->phone)
            ->where('users.country_id', $request->country_id)
            ->first();

        $token = $user->createToken('API Token')->accessToken;
        return response()->json([
            'user' => $user,
            'vendor_name' => $vendor_name->org_name_en ?? null,
            'authorization' => [
                'token' => $token,
                'type' => 'Bearer',
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
        ]);
    }

}
