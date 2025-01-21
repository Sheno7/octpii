<?php

namespace App\Http\Controllers\markets\v1;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class MaServiceCategoryController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['nullable', 'string'],
                'status' => ['nullable', 'integer', 'between:0,1']
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors()->first());
            }
            $serviceCategories = ServiceCategory::orderBy('id', 'asc');

            $data = $serviceCategories->paginate(10);
            return $this->getSuccessResponse(__('retrieved-successfully'), $data);
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function store(Request $request) {
        $serviceCategory = ServiceCategory::create([
            'title_en' => $request->get('title_en'),
            'title_ar' => $request->get('title_ar'),
            'description_en' => $request->get('description_en'),
            'description_ar' => $request->get('description_ar'),
            'sector_id' => $request->get('sector_id'),
            'icon' => 'icon'
        ]);
        return $this->getSuccessResponse(__('saved-successfully'), $serviceCategory);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $serviceCategory = ServiceCategory::with(['sector'])->findOrFail($id);
        return $this->getSuccessResponse('Service retrieved successfully', $serviceCategory);
    }

    public function update(Request $request, $id) {
        $serviceCategory = ServiceCategory::findOrFail($id);
        $serviceCategory = $serviceCategory->update([
            'title_en' => $request->get('title_en'),
            'title_ar' => $request->get('title_ar'),
            'description_en' => $request->get('description_en'),
            'description_ar' => $request->get('description_ar'),
            'sector_id' => $request->get('sector_id'),
        ]);
        return $this->getSuccessResponse(__('updated-successfully'), $serviceCategory);
    }
}
