<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Models\AdditionalInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use App\Traits\ResponseTrait;
class UpAdditionalInformationController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        try {
            $data = AdditionalInformation::paginate(10);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function getById(Request $request)
    {
        try {
            $validation = validator($request->all(), [
                'type' => 'required|string|exists:additional_information,type',
            ]);
            if ($validation->fails()) {
                return $this->getValidationErrorResponse('error', $validation->errors());
            }
            $data = AdditionalInformation::where('type', $request->type)->first();
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
