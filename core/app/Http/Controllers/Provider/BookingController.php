<?php

namespace App\Http\Controllers\Provider;

use App\Enums\Status;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderApp\GetBookings;
use App\Http\Requests\ProviderApp\UpdateBooking;
use App\Http\Resources\BookingResource;
use App\Models\Transaction;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller {
  use ResponseTrait;

  public function index(GetBookings $request) {
    try {
      $provider = Auth::user()->provider;
      $bookings = $provider->bookings()->with(['services']);
      if ($request->get('history', 0)) {
        $bookings = $bookings
          ->where(function ($q) {
            $q
              ->whereDate('date', '<', now())
              ->orWhereIn('status', [Status::BOOKINGCANCELLED, Status::BOOKINGCOMPLETED]);
          });
      } else {
        $bookings = $bookings
          ->whereDate('date', '>=', now())
          ->whereNotIn('status', [Status::BOOKINGCANCELLED]);
      }

      $bookings = $bookings->paginate(10);
      $bookings->data = BookingResource::collection($bookings);

      return $this->getSuccessResponse('Bookings Retrieved Successfully', $bookings);
    } catch (\Throwable $th) {
      return $this->getErrorResponse('Error: Please Try again!', $th);
    }
  }

  public function show($id) {
    try {
      $provider = Auth::user()->provider;
      $booking = $provider->bookings()->with(['services'])->find($id);
      $booking = new BookingResource($booking, true);

      return $this->getSuccessResponse('Booking Retrieved Successfully', $booking);
    } catch (\Throwable $th) {
      return $this->getErrorResponse('Error: Please Try again!', $th);
    }
  }

  public function update(UpdateBooking $request, $id) {
    try {
      $provider = Auth::user()->provider;
      $booking = $provider->bookings()->with(['services'])->find($id);
      $booking->update(['status' => $request->get('status')]);
      $booking = new BookingResource($booking, true);

      return $this->getSuccessResponse('Booking Updated Successfully', $booking);
    } catch (\Throwable $th) {
      return $this->getErrorResponse('Error: Please Try again!', $th);
    }
  }

  public function addTransaction(Request $request, $id) {
    $inputs = $request->all();
    $provider = Auth::user()->provider;
    $booking = $provider->bookings()->with(['services'])->find($id);
    $validator = Validator::make($inputs, [
      'id' => [
        'exists:bookings,id'
      ],
      'amount' => [
        'required',
        'numeric',
        'min:1',
      ],
    ]);
    if ($validator->fails()) {
      return $this->getValidationErrorResponse('Check your inputs', $validator->errors());
    }
    $paid_amount = $booking->completed_transactions->sum('amount');
    if ($booking->total <= $paid_amount) {
      return $this->getErrorResponse(__('already-paid'));
    }
    try {
      DB::beginTransaction();
      $draft_transactions = [];
      $amount = $inputs['amount'];
      $tmp = [
        'amount' => $amount,
        'status' => Status::PAYMENTCOMPLETED,
        // 'payment_method_id' => $inputs['payment_method_id'],
        'type' => TransactionType::IN,
        'date' => now(),
        'provider_id' => $provider->id,
        'booking_id' => $booking->id,
        'package_id' => 0,
        'created_by' => auth()->user()->id,
        'created_at' => now(),
      ];
      $booking->payment_status = Status::PAYMENTCOMPLETED;
      $booking->save();
      $draft_transactions[] = $tmp;

      Transaction::insert($draft_transactions);

      DB::commit();
      return $this->getSuccessResponse('success', 'Transaction added successfully');
    } catch (\Exception $exception) {
      DB::rollBack();
      Log::error($exception->getMessage());
      return $this->getErrorResponse($exception->getMessage());
    }
  }
}
