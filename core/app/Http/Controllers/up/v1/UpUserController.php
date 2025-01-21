<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\up\UserResource;
use App\Models\User;
use App\Traits\ResponseTrait;

class UpUserController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $users = User::with(['country', 'vendor.domain', 'vendor.sectors'])->whereNotNull('created_at')->orderBy('created_at', 'desc')->paginate(10);
        $users->data = UserResource::collection($users);
        return $this->getSuccessResponse(__('retrieved-successfully'), $users);
    }
}
