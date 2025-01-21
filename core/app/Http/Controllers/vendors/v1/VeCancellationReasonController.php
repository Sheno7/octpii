<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCancellationReasonRequest;
use App\Http\Requests\UpdateCancellationReasonRequest;
use App\Http\Resources\CancellationReasonResource;
use App\Models\CancellationReason;
use App\Traits\ResponseTrait;

class VeCancellationReasonController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        // $user = request()->user;
        // $role = empty($user->customer) ? Providers::class : Customers::class;
        $reasons = CancellationReason::paginate(-1);
        $reasons->data = CancellationReasonResource::collection($reasons);
        return $this->getSuccessResponse(__('retrieved_successfully'), $reasons);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCancellationReasonRequest $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CancellationReason $cancellationReason) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CancellationReason $cancellationReason) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCancellationReasonRequest $request, CancellationReason $cancellationReason) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CancellationReason $cancellationReason) {
        //
    }
}
