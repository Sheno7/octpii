<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\CentralCountry;
use App\Models\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use App\Traits\ResponseTrait;

class VeCountriesController extends Controller {
    use ResponseTrait;

    public function dropdown() {
        try {
            $data = Countries::orderBy('title_en', 'asc')->paginate(-1);
            $data->data = CountryResource::collection($data);
            return $this->getSuccessResponse(__('retrieved_successfully'), $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error');
        }
    }

    public function edit(Request $request) {
        try {
            $request->validate([
                'id' => 'required|integer|exists:countries,id',
                'status' => ['required', 'integer', 'between:0,1'],
            ]);
            $country = Countries::findOrFail($request->id);
            if (!$country) {
                return $this->getErrorResponse('error', 'country not found');
            }
            $country->status = $request->status;
            $country->save();
            return $this->getSuccessResponse(__('updated_successfully'));
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error');
        }
    }
}
