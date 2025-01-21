<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\VeServices;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller {
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
            $services = VeServices::orderBy('id', 'ASC');
            if ($request->has('name')) {
                $services->where('title_en', 'like', '%' . $request->input('name') . '%')
                    ->orWhereRaw('LOWER(title_en) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
            }
            if ($request->has('status')) {
                $services->where('status', $request->input('status'));
            }
            $data = $services->paginate(10);
            $data->getCollection()->map(function ($item) {
                $item->cost = $item->cost + $item->markup + $item->base_price;
                return $item;
            });
            return $this->getSuccessResponse('Services retrieved successfully', $data);
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $service = VeServices::with(['providers'])->findOrFail($id);
        $service->cost = $service->cost + $service->markup + $service->base_price;
        $service = new ServiceResource($service);
        return $this->getSuccessResponse('Service retrieved successfully', $service);
    }
}
