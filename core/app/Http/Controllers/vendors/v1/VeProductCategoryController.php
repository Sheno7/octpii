<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Traits\ResponseTrait;

class VeProductCategoryController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $categories = ProductCategory::paginate(10);
        $categories->data = ProductCategoryResource::collection($categories);
        return $this->getSuccessResponse(__('retrieved_successfully'), $categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request) {
        $inputs = $request->validated();
        $category = ProductCategory::create($inputs);
        return $this->getSuccessResponse(__('stored_successfully'), $category);
    }

    /**
     * Display the specified resource.
     */
    public function show() {
        $id = request()->get('id');
        $category = ProductCategory::findOrFail($id);
        return $this->getSuccessResponse(__('retrieved_successfully'), $category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request) {
        $inputs = $request->validated();
        $category = ProductCategory::findOrFail($inputs['id']);
        $category->update($inputs);
        return $this->getSuccessResponse(__('updated_successfully'), $category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy() {
        //
    }
}
