<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\vendors\v1\VeMediaController;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Http\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use App\Traits\ResponseTrait;

class UpServiceCategoryController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $categories = ServiceCategory::with(['sector'])->paginate(request()->get('per_page', 10));
        $categories->data = ServiceCategoryResource::collection($categories);
        return $this->getSuccessResponse(__('retrieved_successfully'), $categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceCategoryRequest $request) {
        $inputs = $request->validated();
        $category = ServiceCategory::create($inputs);
        if ($request->hasFile('icon')) {
            $media = new VeMediaController();
            $file = $request->file('icon');
            $title = now();
            $model_type = 'service_category';
            $model_id = $category->id;
            $media->upload($file, $title, $model_type, $model_id);
        }
        return $this->getSuccessResponse(__('stored_successfully'), $category);
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceCategory $serviceCategory) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceCategoryRequest $request) {
        $inputs = $request->validated();
        $category = ServiceCategory::find($inputs['id']);
        $category->update($inputs);
        if ($request->hasFile('icon')) {
            $media = new VeMediaController();
            $icons = $media->getMediaByModelId('service_category', $inputs['id']);
            foreach ($icons as $icon) {
                $media->deleteMedia($icon['id']);
            }
            $file = $request->file('icon');
            $title = now();
            $model_type = 'service_category';
            $model_id = $category->id;
            $media->upload($file, $title, $model_type, $model_id);
        }
        return $this->getSuccessResponse(__('stored_successfully'), $category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCategory $serviceCategory) {
        //
    }
}
