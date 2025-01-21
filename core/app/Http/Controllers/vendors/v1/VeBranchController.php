<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Traits\ResponseTrait;

class VeBranchController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $branches = Branch::paginate(-1);
        $branches->data = BranchResource::collection($branches);
        return $this->getSuccessResponse(__('retrieved_successfully'), $branches);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request) {
        $inputs = $request->validated();
        $branch = Branch::create($inputs);
        return $this->getSuccessResponse(__('stored_successfully'), $branch);
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request) {
        $inputs = $request->validated();
        $branch = Branch::findOrFail($inputs['id']);
        $branch->update($inputs);
        return $this->getSuccessResponse(__('updated_successfully'), $branch);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch) {
        //
    }
}
