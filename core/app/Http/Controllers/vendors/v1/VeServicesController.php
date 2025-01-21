<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\AreaService;
use App\Models\Services;
use App\Models\Setting;
use App\Models\VeServices;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class VeServicesController extends Controller {
    use ResponseTrait;

    public function index(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['nullable', 'string'],
                'status' => ['nullable', 'integer', 'between:0,1'],
                'include_trashed' => ['nullable', 'boolean'],
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors()->first());
            }
            $services = VeServices::withTrashed($request->get('include_trashed', false))->orderBy('id', 'ASC');
            if ($request->has('name')) {
                $services->where('title_en', 'like', '%' . $request->input('name') . '%')
                    ->orWhereRaw('LOWER(title_en) LIKE ?', ['%' . strtolower($request->input('name')) . '%']);
            }
            if ($request->has('status')) {
                $services->where('status', $request->input('status'));
            }
            $data = $services->paginate(10);
            $data->data = ServiceResource::collection($data);
            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }
    public function dropdown(Request $request) {
        try {
            $result = VeServices::where('title_en', 'LIKE', '%' . strtolower($request->search) . '%')
                ->orWhere('title_ar', 'LIKE', '%' . strtolower($request->search) . '%')
                ->paginate($request->get('per_page', 10));
            $result->data = ServiceResource::collection($result);
            return $this->getSuccessResponse('success', $result);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    // remove service from area coverage , ve_services table
    public function remove_service_offered(Request $request) {
        try {
            $data = $request->validate([
                'id' => 'required|integer|exists:ve_services,id'
            ]);
            if (!$data) {
                return $this->getErrorResponse('error', 'Invalid data.');
            }
            $service = VeServices::where('id', $request->id)->first();
            if (!$service) {
                return $this->getErrorResponse('error', 'Service not found.');
            }
            AreaService::where('service_id', $request->id)->delete();
            $service->delete();
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function change_status(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:ve_services,id',
                'status' => ['integer', 'between:0,1'],
                'visible' => ['boolean']
            ]);
            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors()->first());
            }
            $service = VeServices::where('id', $request->id)->first();
            if (!$service) {
                return $this->getErrorResponse('error', 'Service not found.');
            }
            $service->status = $request->status ?? $service->status;
            $service->visible = $request->visible ?? $service->visible;
            $service->save();
            return $this->getSuccessResponse('success', $service);
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function add(Request $request) {
        try {
            DB::beginTransaction();
            $data = $request->validate([
                'services' => ['required', 'array'],
                'services.*.id' => ['sometimes', 'integer', 'exists:ve_services,id'],
                'services.*.category_id' => ['required', 'integer', 'exists:service_categories,id'],
                'services.*.title_ar' => ['required', 'string'],
                'services.*.title_en' => ['required', 'string'],
                'services.*.description_ar' => ['nullable'],
                'services.*.description_en' => ['nullable'],
                'services.*.coverage' => ['array'],
                'services.*.coverage.*.city_id' => ['integer', 'exists:cities,id'],
                'services.*.coverage.*.areas' => ['array'],
                'services.*.coverage.*.areas.*' => ['integer', 'exists:areas,id'],
                'services.*.pricing_model_id' => ['required', 'integer', 'exists:pricing_models,id'],
                'services.*.cost_per_service' => ['required', 'numeric'],
                'services.*.markup' => ['required', 'numeric'],
                'services.*.duration' => ['required', 'numeric'],
                'services.*.visible' => ['boolean'],
                'services.*.base_price' => ['required', 'integer'],
                'services.*.capacity' => ['required', 'integer'],
                'services.*.capacity_threshold' => ['required', 'integer']
            ]);
            // $service_location = VeServices::where('id', $data['services'][0]['id'])->pluck('service_location')->first();
            foreach ($data['services'] as $serviceData) {
                if (isset($serviceData['id'])) {
                    $service = VeServices::withTrashed()->where('id', $serviceData['id'])->first();

                    if (!$service) {
                        return $this->getErrorResponse('error', 'Service not found.');
                    }
                    $service->restore();
                } else {
                    $serviceData['icon'] = '';
                    $service = VeServices::create($serviceData);
                }
                $service->update([
                    'title_ar' => $serviceData['title_ar'],
                    'title_en' => $serviceData['title_en'],
                    'description_ar' => $serviceData['description_ar'],
                    'description_en' => $serviceData['description_en'],
                    'pricing_model_id' => $serviceData['pricing_model_id'],
                    'cost' => $serviceData['cost_per_service'],
                    'capacity' => $serviceData['capacity'],
                    'capacity_threshold' => $serviceData['capacity_threshold'],
                    'markup' => $serviceData['markup'],
                    'base_price' => $serviceData['base_price'],
                    'duration' => $serviceData['duration'],
                    'visible' => $serviceData['visible'],
                    'status' => 1,
                    'updated_at' => now(),
                ]);
                // if ($service_location == 1) {
                //     $existingAreas = AreaService::where('service_id', $serviceData['id'])->pluck('area_id')->toArray();
                //     $newAreas = array_merge(...array_column($serviceData['coverage'], 'areas'));

                //     foreach ($newAreas as $area) {
                //         AreaService::updateOrCreate(
                //             ['area_id' => $area, 'service_id' => $serviceData['id']],
                //             ['status' => 1, 'updated_at' => now()]
                //         );
                //     }

                //     $areasToDelete = array_diff($existingAreas, $newAreas);
                //     AreaService::whereIn('area_id', $areasToDelete)
                //         ->where('service_id', $serviceData['id'])
                //         ->update(['status' => 0, 'updated_at' => now()]);
                // }
            }
            DB::commit();
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage(), 500);
        }
    }

    public function list_with_trash() {
        try {
            $services = VeServices::onlyTrashed()->orderBy('id', 'ASC')->paginate(10);
            $services->data = ServiceResource::collection($services);
            return $this->getSuccessResponse('success', $services);
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function show(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'id' => 'required|integer|exists:ve_services,id',
            ]);
            if ($validate->fails()) {
                return $this->getErrorResponse('error', $validate->errors()->first());
            }
            $data = VeServices::withTrashed()->findOrFail($request->id);
            $coverageData = [];
            $coverage = VeServices::join('area_service', 've_services.id', '=', 'area_service.service_id')
                ->join('areas', 'area_service.area_id', '=', 'areas.id')
                ->select('areas.id', 'areas.city_id', 've_services.base_price')
                ->where('ve_services.id', $request->id)
                ->where('area_service.status', 1)
                ->get();
            foreach ($coverage as $item) {
                $cityId = $item->city_id;
                $areaId = $item->id;
                if (!isset($coverageData[$cityId])) {
                    $coverageData[$cityId] = ['city_id' => $cityId, 'areas' => []];
                }
                $coverageData[$cityId]['areas'][] = $areaId;
            }
            $data->coverage = array_values($coverageData);
            //            $rangs = VeServices::join('pricing_model_data', 've_services.pricing_model_id', '=', 'pricing_model_data.pricing_models_id')
            //                ->select(
            //                    DB::raw('CAST(pricing_model_data.min AS integer) as from'),
            //                    DB::raw('CAST(pricing_model_data.max AS integer) as to'),
            //                    'pricing_model_data.price'
            //                )
            //                ->get();
            //            $data->ranges = $rangs;
            $data->sector_id = $data->category?->sector_id;
            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }
}
