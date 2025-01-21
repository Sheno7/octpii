<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Models\Areas;
use App\Models\Cities;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class VeAreasController extends Controller {

    use ResponseTrait;
    public function index(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'status' => ['nullable', 'integer', 'between:0,1'],
                'area_id' => ['nullable', 'integer', 'exists:areas,id'],
            ]);
            if ($validate->fails()) {
                return $this->getErrorResponse('error', $validate->errors()->first());
            }
            $data = Areas::join('cities', 'areas.city_id', '=', 'cities.id')
                ->select(
                    'areas.id',
                    'areas.title_en',
                    'areas.status',
                    'areas.title_ar',
                    'cities.id as city_id',
                    'cities.title_en as city_title_en',
                    'cities.title_ar as city_title_ar'
                )
                //->where('areas.status', 1)
                ->where('cities.status', 1)
                ->orderBy('areas.title_en', 'ASC');
            if ($request->has('area_id')) {
                $data->where('areas.id', $request->area_id);
            }
            if ($request->has('status')) {
                $data->where('areas.status', $request->status);
            }
            $response = $data->paginate(-1);
            $response->data = AreaResource::collection($response);
            return $this->getSuccessResponse('success', $response);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }

    protected function changeStatus(Request $request) {
        try {
            $request->validate([
                'id' => 'required|integer|exists:areas,id',
                'status' => ['required', 'integer', 'between:0,1'],
            ]);
            $area = Areas::find($request->id);
            if (!$area) {
                return $this->getErrorResponse('error', 'area not found');
            }
            $area->status = $request->status;
            $area->save();
            $areasCount = Areas::where('city_id', $area->city_id)->where('status', 1)->count();
            if ($areasCount === 0) {
                $city = Cities::find($area->city_id);
                if ($city) {
                    DB::table('cities')->where('id', $area->city_id)
                        ->update(['status' => 0, 'updated_at' => now()]);
                }
                return $this->getErrorResponse('error', 'city not found');
            }
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }
}
