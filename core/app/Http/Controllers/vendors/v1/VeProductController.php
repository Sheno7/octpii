<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\ResponseTrait;

class VeProductController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $products = Product::with(['category'])->paginate(10);
        $products->data = ProductResource::collection($products);
        return $this->getSuccessResponse(__('retrieved_successfully'), $products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request) {
        $inputs = $request->validated();
        $inputs['branch_id'] = $request->get('branch_id', $request->selected_branch);
        $product = Product::create($inputs);
        return $this->getSuccessResponse(__('stored_successfully'), $product);
    }

    /**
     * Display the specified resource.
     */
    public function show() {
        $id = request()->get('id');
        $product = Product::findOrFail($id);
        return $this->getSuccessResponse(__('retrieved_successfully'), $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request) {
        $inputs = $request->validated();
        $product = Product::findOrFail($inputs['id']);
        $product->update($inputs);
        return $this->getSuccessResponse(__('updated_successfully'), $product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy() {
        //
    }
}
