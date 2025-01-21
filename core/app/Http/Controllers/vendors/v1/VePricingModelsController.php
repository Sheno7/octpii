<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Models\PricingModels;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log as Logger;

class VePricingModelsController extends Controller
{
    use ResponseTrait;
    public function dropdown()
    {
        try {
            //$sector = Setting::where('key', 'sector')->value('value');
//            $pricingModels = PricingModelSector::join('pricing_models', 'pricing_models.id', '=', 'pricing_model_sector.pricing_model_id')
//                ->select('pricing_models.*')
//            ->paginate(10);
            $pricingModels = PricingModels::paginate(10);
            return $this->getSuccessResponse('success', $pricingModels);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
