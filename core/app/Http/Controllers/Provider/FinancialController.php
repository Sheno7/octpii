<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\Provider\TransactionResource;
use App\Models\Transaction;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FinancialController extends Controller {
  use ResponseTrait;

  public function index(Request $request) {
    try {
      $validator = Validator::make($request->all(), [
        'month' => 'nullable|integer|between:1,12',
        'year' => 'nullable|integer|min:1900|max:' . date('Y'),
      ]);
      if ($validator->fails()) {
        return $this->getValidationErrorResponse($validator->errors());
      }
      $provider = $request->user()->provider;
      if (!$provider) {
        return $this->getErrorResponse('provider not found');
      }

      $month = $request->get('month', (int)date('m'));
      $year = $request->get('year', (int)date('Y'));

      $transactions = Transaction::where('provider_id', $provider->id)
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

      $transactions->data = TransactionResource::collection($transactions);

      return $this->getSuccessResponse(__('retrieved-successfully'), $transactions);
    } catch (\Exception $exception) {
      Log::error($exception->getMessage());
      return $this->getErrorResponse($exception->getMessage());
    }
  }
}
