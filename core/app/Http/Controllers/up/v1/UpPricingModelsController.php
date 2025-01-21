<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Models\PricingModels;
use App\Models\PricingModelSector;
use App\Models\Vendors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log as Logger;
use App\Traits\ResponseTrait;
use App\Traits\TenantTrait;
class UpPricingModelsController extends Controller
{
    use ResponseTrait, TenantTrait;
    public function index()
    {
        try {
            $data = PricingModels::select('id', 'name', 'capacity', 'variable_name','base_price','min_price',
                'pricing_type', 'capacity_threshold', 'additional_cost', 'markup', 'created_at')
                ->orderBy('id', 'desc')
                ->paginate(10);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function add(Request $request)
    {
        try {
            // validate data first
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'capacity' => 'required|boolean',
                'variable_name' => 'required|string',
                'pricing_type' => 'required|in:fixed,variable',
                'capacity_threshold' => 'required|boolean',
                'markup' => 'required|boolean'
            ]);
            if ($validator->fails()) {
                return $this->getValidationErrorResponse($validator->errors());
            }
            $pricingModel = new PricingModels();
            $pricingModel->name = $request->name;
            $pricingModel->capacity = $request->capacity;
            $pricingModel->variable_name = $request->variable_name;
            $pricingModel->pricing_type = $request->pricing_type;
            $pricingModel->capacity_threshold = $request->capacity_threshold;
            $pricingModel->markup = $request->markup;
            $pricingModel->base_price = $request->base_price;
            $pricingModel->min_price = $request->min_price ?? false;
            $pricingModel->created_at = now();
            $pricingModel->updated_at = now();
            $pricingModel->upid = $pricingModel->id;
            $pricingModel->save();
            $this->trigger($pricingModel);
            return $this->getSuccessResponse('success', $pricingModel);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'capacity' => 'required|boolean',
                'variable_name' => 'required|string',
                'pricing_type' => 'required|in:fixed,variable',
                'capacity_threshold' => 'required|boolean',
                'markup' => 'required|boolean',
                'base_price' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return $this->getValidationErrorResponse($validator->errors());
            }

            $pricingModel = PricingModels::findorfail($request->id);
            $pricingModel->name = $request->name;
            $pricingModel->capacity = $request->capacity;
            $pricingModel->variable_name = $request->variable_name;
            $pricingModel->pricing_type = $request->pricing_type;
            $pricingModel->capacity_threshold = $request->capacity_threshold;
            $pricingModel->markup = $request->markup;
            $pricingModel->base_price = $request->base_price;
            $pricingModel->min_price = $request->min_price ?? false;
            $pricingModel->updated_at = now();
            $pricingModel->save();
            $this->trigger($pricingModel);
            return $this->getSuccessResponse('success', $pricingModel);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function dropdown(Request $request)
    {
        try {
            $pricingModels = DB::table('pricing_models')
                ->select('id', 'name')
                ->where(DB::raw('LOWER(name)'), 'LIKE', '%' . strtolower($request->name) . '%')
                ->get();
            return $this->getSuccessResponse('success', $pricingModels);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            if (!$request->id) {
                return $this->getErrorResponse('error', 'id is required');
            }
            $pricingModel = PricingModels::findorfail($request->id);
            $pricingModel->delete();
            // delete all services related to this pricing model
            $pricingModel->services()->delete();
            $this->trigger($pricingModel);
            return $this->getSuccessResponse('success', 'deleted');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }


    protected function trigger($pricingModels)
    {
        try {
            // get sector related with prcing model
            $sectorId  = PricingModelSector::where('pricing_model_id', $pricingModels->id)->pluck('sector_id');
            // get active vendors
            $vendors = Vendors::where('status', 1)->where('sector_id', $sectorId)->get();
            foreach ($vendors as $vendor) {
                // switch to tenant database
                $this->switch_tenant($vendor->id, 'pricing_models')
                    ->where('upid', $pricingModels->upid)->updateOrInsert
                    (
                        ['upid' => $pricingModels->upid],
                        [
                            'name' => $pricingModels->name,
                            'capacity' => $pricingModels->capacity,
                            'variable_name' => $pricingModels->variable_name,
                            'pricing_type' => $pricingModels->pricing_type,
                            'capacity_threshold' => $pricingModels->capacity_threshold,
                            'additional_cost' => $pricingModels->additional_cost,
                            'markup' => $pricingModels->markup,
                            //'updated_at' => $pricingModels->updated_at,
                        ]
                    );
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }


}
