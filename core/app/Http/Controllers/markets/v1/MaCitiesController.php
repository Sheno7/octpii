<?php

namespace App\Http\Controllers\markets\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\CentralArea;
use App\Models\CentralCity;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;

class MaCitiesController extends Controller {
    use ResponseTrait;
    public function listCities(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'status' => ['nullable', 'integer', 'between:0,1'],
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            ]);
            if ($validate->fails()) {
                return $this->getErrorResponse('error', $validate->errors()->first());
            }
            $data = CentralCity::whereHas('country', function ($query) {
                $query->where('status', 1);
            })->orderBy('title_en', 'ASC');

            if ($request->has('status')) {
                $data->where('cities.status', $request->status);
            }

            if ($request->has('city_id')) {
                $data->where('cities.id', $request->city_id);
            }

            $response = $data->paginate(100000);
            $response->data = CityResource::collection($response);
            return $this->getSuccessResponse('success', $response);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function listAreas(Request $request) {
        try {
            $data = CentralArea::join('cities', 'areas.city_id', '=', 'cities.id')
                ->select(
                    'areas.id',
                    'areas.title_ar',
                    'areas.title_en',
                    'areas.city_id',
                    'areas.lat',
                    'areas.long',
                    'cities.title_en AS city',
                    'areas.created_at'
                )
                ->orderBy('id', 'desc')
                ->paginate(10);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
