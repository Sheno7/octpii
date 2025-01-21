<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\Cities;
use App\Models\Countries;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;

class VeCitiesContrlller extends Controller {
    use ResponseTrait;
    public function list(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'status' => ['nullable', 'integer', 'between:0,1'],
                'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            ]);
            if ($validate->fails()) {
                return $this->getErrorResponse('error', $validate->errors()->first());
            }
            $data = Cities::whereHas('country', function ($query) {
                $query->where('status', 1);
            })->orderBy('title_en', 'ASC');

            if ($request->has('status')) {
                $data->where('cities.status', $request->status);
            }

            if ($request->has('city_id')) {
                $data->where('cities.id', $request->city_id);
            }

            $response = $data->paginate(10);
            $response->data = CityResource::collection($response);
            return $this->getSuccessResponse('success', $response);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function change_status(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|int|exists:cities,id',
                'status' => ['required', 'integer', 'between:0,1'],
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors()->first());
            }
            $city = Cities::find($request->id);
            if (!$city) {
                return $this->getErrorResponse('error', 'city not found');
            }
            $city->status = $request->status;
            $city->save();
            // if all cities in country is inactive, then country will be inactive
            $county = Cities::where('country_id', $city->country_id)->where('status', 1)->count();
            if ($county == 0) {
                $country = Countries::find($city->country_id);
                $country->status = 0;
                $country->save();
            }
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
