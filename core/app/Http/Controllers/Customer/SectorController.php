<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\SectorResource;
use App\Http\Resources\ServiceResource;
use App\Models\Sectors;
use App\Models\VeServices;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class SectorController extends Controller {
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
            $sectors = Sectors::orderBy('id', 'ASC');
            if ($request->has('name')) {
                $sectors->where('title_en', 'like', '%' . $request->input('name') . '%')
                    ->orWhereRaw('LOWER(title_en) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
            }
            if ($request->has('status')) {
                $sectors->where('status', $request->input('status'));
            }
            $data = $sectors->paginate(10);

            return $this->getSuccessResponse('Sectors retrieved successfully', SectorResource::collection($data));
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $sector = Sectors::with(['categories'])->findOrFail($id);
        $sector = new SectorResource($sector);
        return $this->getSuccessResponse('Sector retrieved successfully', $sector);
    }
}
