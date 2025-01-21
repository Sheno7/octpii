<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;
use LucasDotVin\Soulbscription\Models\Plan;

class UpPlanController extends Controller {
    use ResponseTrait;

    public function index() {
        try {
            $data = Plan::paginate(-1);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
