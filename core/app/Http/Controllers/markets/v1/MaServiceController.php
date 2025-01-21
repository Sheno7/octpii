<?php

namespace App\Http\Controllers\markets\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\ServiceResource as CustomerServiceResource;
use App\Models\Services;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class MaServiceController extends Controller {
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
            $services = Services::orderBy('id', 'asc');

            if ($request->has('name')) {
                $services->where('title_en', 'like', '%' . $request->input('name') . '%')
                    ->orWhereRaw('LOWER(title_en) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
            }
            if ($request->has('status')) {
                $services->where('status', $request->input('status'));
            }

            $data = $services->paginate(10);
            return $this->getSuccessResponse('Services retrieved successfully', $data);
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function store(Request $request) {
        $service = Services::create([
            'title_en' => $request->get('title_en'),
            'title_ar' => $request->get('title_ar'),
            'description_en' => $request->get('description_en'),
            'description_ar' => $request->get('description_ar'),
            'category_id' => $request->get('category_id'),
        ]);
        return $this->getSuccessResponse(__('saved-successfully'), $service);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $service = Services::with(['sectors', 'category'])->findOrFail($id);
        $service->cost = $service->cost + $service->markup + $service->base_price;
        return $this->getSuccessResponse('Service retrieved successfully', $service);
    }

    public function update(Request $request, $id) {
        $service = Services::findOrFail($id);
        $service = $service->update([
            'title_en' => $request->get('title_en'),
            'title_ar' => $request->get('title_ar'),
            'description_en' => $request->get('description_en'),
            'description_ar' => $request->get('description_ar'),
            'category_id' => $request->get('category_id'),
        ]);
        return $this->getSuccessResponse(__('updated-successfully'), $service);
    }
}
