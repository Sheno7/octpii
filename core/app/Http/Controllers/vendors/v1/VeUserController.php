<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Vendors;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VeUserController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $users = User::with(['customer:id,user_id', 'provider:id,user_id', 'country:id,code']);
        $users->when(request()->has('role'), function ($q) {
            $roles = explode(',', request()->get('role'));
            $q->role($roles);
        });
        $users = $users->paginate();
        $users->data = UserResource::collection($users);
        return $this->getSuccessResponse(__('retrieved_successfully'), $users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request) {
        $inputs = $request->validated();
        $vendor =  tenant()->domains->first()->vendor;
        // if ($vendor->cantConsume('admins', 1)) {
        //     return $this->getErrorResponse('quota-exceeded');
        // }
        DB::beginTransaction();
        $user = User::create([
            'first_name' => $inputs['first_name'],
            'last_name' => $inputs['last_name'],
            'name' => $inputs['first_name'] . ' ' . $inputs['last_name'],
            'phone' => $inputs['phone'],
            'country_id' => $inputs['country_id'],
            'password' => Hash::make($inputs['password'])
        ]);
        $vendor->consume('admins', 1);
        DB::commit();
        $user->assignRole($inputs['role']['name']);
        return $this->getSuccessResponse(__('stored_successfully'), $user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request) {
        $inputs = $request->validated();
        $user = User::findOrFail($inputs['id']);
        if (isset($inputs['password'])) {
            $inputs['password'] = Hash::make($inputs['password']);
            unset($inputs['password_confirmation']);
        }
        $user->update($inputs);
        return $this->getSuccessResponse(__('updated_successfully'), $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user) {
        //
    }
}
