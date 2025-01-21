<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Models\AdditionalInformation;
use App\Models\PricingModelSector;
use App\Models\SectorAdditionalInformation;
use App\Models\Sectors;
use App\Models\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class UpSectorsController extends Controller {
    use ResponseTrait;

    public function index() {
        try {
            $sectors = Sectors::withCount('services')
                ->with('categories')
                ->with('pricingModels')
                ->with('sectorAdditionalInformation')
                ->has('pricingModels')
                //  ->has('sectorAdditionalInformation')
                ->orderBy('id', 'desc')
                ->paginate(10);

            $sectors->getCollection()->transform(function ($sector) {
                $pricingModelIds = $sector->pricingModels->pluck('pricing_model_id')->toArray();
                $additionalInformationIds = optional($sector->sectorAdditionalInformation)
                    ->pluck('additional_information_id')
                    ->toArray() ?? [];
                $this->check();
                return [
                    'id' => $sector->id,
                    'title_ar' => $sector->title_ar,
                    'title_en' => $sector->title_en,
                    'status' => $sector->status,
                    'icon' => $sector->icon,
                    'created_at' => $sector->created_at,
                    'updated_at' => $sector->updated_at,
                    'deleted_at' => $sector->deleted_at,
                    'pricing_models' => $pricingModelIds,
                    'additional_information' => $additionalInformationIds,
                    'multi_sessions' => $sector->multi_sessions,
                    'customer_rating' => $sector->customer_rating,
                    'services_count' => $sector->services_count,
                    'categories' => $sector->categories,
                ];
            });

            return $this->getSuccessResponse('success', $sectors);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function add(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'title_ar' => 'required',
                'title_en' => 'required',
                'pricing_models' => ['required', 'array'],
                'pricing_models.*' => ['required', 'integer', 'exists:pricing_models,id'],
                'additional_info' => ['sometimes', 'array'],
                'additional_info.*.title' => ['sometimes', 'string'],
                'additional_info.*.hasfiles' => ['sometimes'],
                'icon' => 'required',
                'customer_rating' => 'integer|between:0,1',
                'multi_sessions' => 'integer|between:0,1',
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse($validator->errors());
            }
            DB::beginTransaction();
            $sector = new Sectors();
            $sector->title_ar = request('title_ar');
            $sector->title_en = request('title_en');
            $sector->status = request('status', 0);
            $sector->multi_sessions = request('multi_sessions', 0);
            $sector->customer_rating = request('customer_rating', 0);
            if (request()->hasFile('icon')) {
                $iconFile = request()->file('icon');
                $iconFileName = time() . '.' . $iconFile->getClientOriginalExtension();
                $iconFile->move(public_path('uploads/sectors'), $iconFileName);
                $sector->icon = url('uploads/sectors/' . $iconFileName);
            }
            $sector->created_at = now();
            $sector->updated_at = now();
            $sector->save();
            foreach (request('pricing_models') as $pricingModel) {
                $pricingModelSector = new PricingModelSector();
                $pricingModelSector->pricing_model_id = $pricingModel;
                $pricingModelSector->sector_id = $sector->id;
                $pricingModelSector->created_at = now();
                $pricingModelSector->updated_at = now();
                $pricingModelSector->save();
            }
            DB::commit();
            $additionalInformationIds = [];
            foreach ($request->input('additional_info', []) as $additionalInfo) {
                $existingAdditionalInformation = AdditionalInformation::updateOrCreate([
                    'type' => $additionalInfo['title']
                ], [
                    'type' => $additionalInfo['title'],
                    'hasfile' => $additionalInfo['hasfiles']
                ]);
                $additionalInformationIds[] = $existingAdditionalInformation->id;
            }
            $sector->additionalInformation()->sync($additionalInformationIds);
            return $this->getSuccessResponse('success', $sector);
        } catch (\Throwable $th) {
            DB::rollBack();
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
    public function edit(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric',
                'pricing_models' => ['array'],
                'pricing_models.*' => ['integer'],
                'additional_information' => ['array'],
                'additional_information.*' => ['integer'],
                'title_en' => 'string',
                'title_ar' => 'string',
                'customer_rating' => 'integer|between:0,1',
                'multi_sessions' => 'integer|between:0,1',
            ]);

            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors());
            }

            $sector = Sectors::findOrFail($request->id);
            $sector->fill($request->only('title_ar', 'title_en'));
            $sector->multi_sessions = $request->input('multi_sessions') ?? $sector->multi_sessions;
            $sector->customer_rating = $request->input('customer_rating') ?? $sector->customer_rating;
            $sector->status = $request->input('status', $sector->status);

            if ($request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $iconFileName = time() . '.' . $iconFile->getClientOriginalExtension();
                $iconFile->move(public_path('uploads/sectors'), $iconFileName);
                $sector->icon = url('uploads/sectors/' . $iconFileName);
            }

            $sector->save();

            if ($request->has('pricing_models')) {
                $pricingModelIds = $request->input('pricing_models', []);
                $sector->pricingModelsSector()->sync($pricingModelIds);
            }

            $additionalInformationIds = [];
            foreach ($request->input('additional_info', []) as $additionalInfo) {
                $existingAdditionalInformation = AdditionalInformation::updateOrCreate([
                    'type' => $additionalInfo['title']
                ], [
                    'type' => $additionalInfo['title'],
                    'hasfile' => $additionalInfo['hasfiles']
                ]);
                $additionalInformationIds[] = $existingAdditionalInformation->id;
            }
            $sector->additionalInformation()->sync($additionalInformationIds);

            return $this->getSuccessResponse('success', $sector);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function changeStatus(Request $request) {
        try {
            // validate
            $validator = validator($request->all(), [
                'status' => 'required|in:0,1', // 1 => active , 0 => inactive
                'id' => 'required|exists:sectors,id'
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse($validator->errors());
            }

            $sector = Sectors::findorfail($request->id);
            $sector->status = $request->status;
            $sector->updated_at = now();
            $sector->save();
            $this->check();
            return $this->getSuccessResponse('success', $sector);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function destroy(Request $request) {
        try {
            $sector = Sectors::findorfail($request->id);
            $sector->delete();
            // delete service associated with sector
            Services::where('sector_id', $request->id)->delete();
            $this->check();
            return $this->getSuccessResponse('success', 'deleted');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function dropdown(Request $request) {
        try {
            $sectors = DB::table('sectors')->select('id', 'title_en')->where(DB::raw('LOWER(title_en)'), 'LIKE', '%' . strtolower($request->title_en) . '%')
                ->orderBy('id', 'desc')->get();
            return $this->getSuccessResponse('success', $sectors);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error');
        }
    }

    protected function check() {
        try {
            DB::beginTransaction();
            $sectors = Sectors::where('status', 1)->get();
            foreach ($sectors as $sector) {
                $services = Services::where('sector_id', $sector->id)->where('status', 1)->count();
                if ($services === 0) {
                    $sector->status = 0;
                    $sector->save();
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function list_register() {
        try {
            $this->check();
            $sectors = Sectors::withCount('services')
                ->with('pricingModels')
                ->has('pricingModels')
                ->orderBy('id', 'desc')
                ->where('status', '!=', 0)
                ->paginate(10);

            $sectors->getCollection()->transform(function ($sector) {
                // Extract pricing model IDs as an array
                $pricingModelIds = $sector->pricingModels->pluck('pricing_model_id')->toArray();

                return [
                    'id' => $sector->id,
                    'title_ar' => $sector->title_ar,
                    'title_en' => $sector->title_en,
                    'status' => $sector->status,
                    'icon' => $sector->icon,
                    'created_at' => $sector->created_at,
                    'updated_at' => $sector->updated_at,
                    'deleted_at' => $sector->deleted_at,
                    'pricing_models' => $pricingModelIds,
                    'services_count' => $sector->services_count,
                ];
            });

            return $this->getSuccessResponse('success', $sectors);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
