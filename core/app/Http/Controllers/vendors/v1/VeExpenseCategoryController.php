<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use App\Traits\ResponseTrait;

class VeExpenseCategoryController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $categories = ExpenseCategory::paginate(10);
        $categories->data = ExpenseCategoryResource::collection($categories);
        return $this->getSuccessResponse(__('retrieved_successfully'), $categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseCategoryRequest $request) {
        $inputs = $request->validated();
        $category = ExpenseCategory::create($inputs);
        return $this->getSuccessResponse(__('stored_successfully'), $category);
    }

    /**
     * Display the specified resource.
     */
    public function show() {
        $id = request()->get('id');
        $category = ExpenseCategory::findOrFail($id);
        return $this->getSuccessResponse(__('retrieved_successfully'), $category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseCategoryRequest $request) {
        $inputs = $request->validated();
        $category = ExpenseCategory::findOrFail($inputs['id']);
        $category->update($inputs);
        return $this->getSuccessResponse(__('updated_successfully'), $category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy() {
        $id = request()->get('id');
        $category = ExpenseCategory::findOrFail($id);
        return $this->getSuccessResponse(__('deleted_successfully'), $category);
    }
}
