<?php

namespace App\Http\Controllers\markets\v1;

use App\Enums\Status;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\CreateTransaction;
use App\Http\Requests\Transaction\GetCustomerBalance;
use App\Http\Requests\Transaction\GetTransactions;
use App\Http\Requests\Transaction\UpdateTransaction;
use App\Http\Resources\TransactionResource;
use App\Models\Booking;
use App\Models\BookingVendor;
use App\Models\MaTransaction;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class MaTransactionsController extends Controller {
    use ResponseTrait;

    public function index(GetTransactions $request) {
        try {
            $data = MaTransaction::with(['paymentMethod', 'package', 'vendor', 'booking', 'expense', 'procurement'])
                ->orderBy('transaction.id', 'desc');

            $this->applyFilters($data, $request);

            $data = $data->paginate(10);

            $data->data = TransactionResource::collection($data);

            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }

    private function applyFilters($query, $request) {
        $branch_id = $request->get('branch_id', $request->get('selected_branch', null));
        if ($branch_id) {
            $query->where(function ($query) use ($branch_id) {
                $query->whereHas('booking', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                })->orWhereHas('package', function ($q) use ($branch_id) {
                    $q->whereHas('booking', function ($q) use ($branch_id) {
                        $q->where('branch_id', $branch_id);
                    });
                })->orWhereHas('expense', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                })->orWhereHas('procurement', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                })->orWhereHas('vendor', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                });
            });
        }

        $query->when($request->has('booking_id'), function ($q) use ($request) {
            $q->where('booking_id', $request->input('booking_id'));
        });

        $query->when($request->has('transaction_at_start'), function ($q) use ($request) {
            $startDate = $request->input('transaction_at_start');
            $q->when($request->has('transaction_at_end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('transaction_at_end');
                $q->whereBetween('date', [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->whereDate('date', '>=', $startDate);
            });
        });

        $query->when($request->has('created_at_start'), function ($q) use ($request) {
            $startDate = $request->input('created_at_start');
            $q->when($request->has('created_at_end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('created_at_end');
                $q->whereBetween('created_at', [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            });
        });

        $query->when($request->has('payment_status'), function ($q) use ($request) {
            $q->where('status', $request->input('payment_status'));
        });

        $query->when($request->has('services'), function ($q) use ($request) {
            $serviceIds = explode(',', $request->input('services'));
            $q->where(function ($query) use ($serviceIds) {
                $query->whereHas('booking.services', function ($qs) use ($serviceIds) {
                    $qs->whereIn('service_id', $serviceIds);
                })->orWhereHas('package.services', function ($qs) use ($serviceIds) {
                    $qs->whereIn('service_id', $serviceIds);
                });
            });
        });

        $query->when($request->has('vendor_id'), function ($q) use ($request) {
            $q->where('vendor_id', $request->input('vendor_id'));
        });

        $query->when($request->has('customer_id'), function ($q) use ($request) {
            $customerId = $request->input('customer_id');
            $q->where(function ($query) use ($customerId) {
                $query->whereHas('booking', function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId);
                })->orWhereHas('package', function ($q) use ($customerId) {
                    $q->where('customer_id', $customerId);
                });
            });
        });
    }

    public function getCustomerTotal(GetCustomerBalance $request) {
        $inputs = $request->validated();
        try {
            $customer_outstanding = $this->getCustomerOutstanding($inputs['id']);
            return $this->getSuccessResponse('success', [
                'total' => $customer_outstanding['outstanding'],
                'transaction' => $customer_outstanding['transactions']
            ]);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }

    public function addTransaction(CreateTransaction $request) {
        $inputs = $request->validated();
        return $this->createTransactions($inputs);
    }

    public function update(UpdateTransaction $request) {
        $inputs = $request->validated();
        $transaction = MaTransaction::findOrFail($inputs['id']);
        if ($transaction->type !== TransactionType::IN) {
            return $this->getErrorResponse(__("cannot_edit_transaction"));
        }
        $amount_diff = $inputs['amount'] - $transaction->amount;
        $related = $transaction->booking ?? $transaction->package;

        if ($amount_diff <= 0) {
            if ($amount_diff < 0) {
                $related->payment_status = Status::PAYMENTPARTIAL;
                $related->save();
                $transaction->amount = $inputs['amount'];
            }
            $transaction->date = $inputs['date'];
            $transaction->payment_method_id = $inputs['payment_method_id'];
            $transaction->save();
            return $this->getSuccessResponse('success', 'Transaction updated successfully');
        }

        $inputs['amount'] = $amount_diff;
        $inputs['id'] = $inputs['customer_id'];
        return $this->createTransactions($inputs);
    }

    public function list_payment_method() {
        try {
            $data = PaymentMethod::where('status', Status::ACTIVE)
                ->get();
            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }

    private function getCustomerUnpaidBookings($customer_id) {
        try {
            return Booking::with(['completed_transactions'])
                ->where('customer_id', $customer_id)
                ->whereNull('package_id')
                ->where('status', Status::BOOKINGCOMPLETED)
                ->whereIn('payment_status', [Status::PAYMENTPENDING, Status::PAYMENTPARTIAL])
                ->get(['id', 'customer_id', 'package_id', 'status', 'payment_status', 'total']);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return collect([]);
        }
    }

    private function getCustomerUnpaidPackages($customer_id) {
        try {
            return Package::with(['services:id,title_en', 'completed_transactions'])
                ->where('customer_id', $customer_id)
                ->whereNotIn('status', [Status::PACKAGESERVICECANCELLED])
                ->whereIn('status', [Status::PACKAGESERVICESTARTED, Status::PACKAGESERVICECOMPLETED])
                ->whereIn('payment_status', [Status::PAYMENTPENDING, Status::PAYMENTPARTIAL])
                ->get(['id', 'customer_id', 'status', 'payment_status']);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return collect([]);
        }
    }

    public function getCustomerOutstanding($customer_id) {
        try {
            $bookings = $this->getCustomerUnpaidBookings($customer_id);
            $packages = $this->getCustomerUnpaidPackages($customer_id);
            $transactions = $bookings->flatMap(function ($booking) {
                return $booking->completed_transactions;
            })->merge($packages->flatMap(function ($package) {
                return $package->completed_transactions;
            }));

            $total_bookings = $bookings->sum('total');
            $total_packages = $packages->flatMap(function ($package) {
                return $package->services->map(function ($service) {
                    if ($service->pivot->price > 0) {
                        return $service->pivot->price;
                    }
                    return 0;
                });
            })->sum();
            $total_transactions = $transactions->sum('amount');

            $outstanding = $total_bookings + $total_packages - $total_transactions;

            return [
                'bookings' => $bookings,
                'packages' => $packages,
                'transactions' => $transactions,
                'outstanding' => intval($outstanding),
            ];
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return 0; // Return 0 or handle the error as appropriate
        }
    }

    private function getCustomerTotalBookingSpent($customerId) {
        try {
            $total = MaTransaction::join('booking', 'booking.id', '=', 'transaction.booking_id')
                ->where('booking.customer_id', $customerId)
                ->where('booking.package_id', null)
                ->where('transaction.status', Status::PAYMENTCOMPLETED)
                ->sum('transaction.amount');
            return intval($total);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return 0;
        }
    }

    private function getCutomerTotalPackageSpent($customerId) {
        try {
            $total = MaTransaction::join('package', 'package.id', '=', 'transaction.package_id')
                ->where('package.customer_id', $customerId)
                ->where('transaction.status', Status::PAYMENTCOMPLETED)
                ->sum('transaction.amount');
            return intval($total);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return 0;
        }
    }

    public function getCustomerTotalSpent($customerId) {
        try {
            $total = $this->getCustomerTotalBookingSpent($customerId) + $this->getCutomerTotalPackageSpent($customerId);
            return intval($total);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return 0;
        }
    }

    private function createTransactions($inputs) {
        $customer_outstanding = $this->getCustomerOutstanding($inputs['id']);
        $validator = Validator::make($inputs, [
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:' . $customer_outstanding['outstanding'],
            ],
        ]);
        if ($validator->fails()) {
            return $this->getValidationErrorResponse('Check your inputs', $validator->errors());
        }
        try {
            DB::beginTransaction();
            $draft_transactions = [];
            $amount = $inputs['amount'];

            foreach ($customer_outstanding['bookings'] as $booking) {
                $booking_amount = $booking['total'] - $booking->completed_transactions->sum('amount');
                $tmp = [
                    'amount' => $booking_amount,
                    'status' => Status::PAYMENTCOMPLETED,
                    'payment_method_id' => $inputs['payment_method_id'],
                    'type' => TransactionType::IN,
                    'date' => $inputs['date'],
                    'vendor_id' => BookingVendor::where('booking_id', $booking->id)->value('vendor_id'),
                    'booking_id' => $booking->id,
                    'package_id' => 0,
                    'created_by' => auth()->user()->id,
                    'created_at' => now(),
                ];
                if ($booking_amount >= $amount) {
                    $tmp['amount'] = $amount;
                    $amount = 0;
                    $draft_transactions[] = $tmp;
                    $booking->payment_status = Status::PAYMENTPARTIAL;
                    $booking->save();
                    break;
                }
                $amount -= $booking_amount;
                $booking->payment_status = Status::PAYMENTCOMPLETED;
                $booking->save();
                $draft_transactions[] = $tmp;
            }
            if ($amount > 0) {
                foreach ($customer_outstanding['packages'] as $package) {
                    $total = $package->services->map(function ($service) {
                        if ($service->pivot->price > 0) {
                            return $service->pivot->price;
                        }
                        return 0;
                    })->sum();
                    $outstanding = $total - $package->completed_transactions->sum('amount');
                    $tmp = [
                        'amount' => $outstanding,
                        'status' => Status::PAYMENTCOMPLETED,
                        'payment_method_id' => $inputs['payment_method_id'],
                        'type' => TransactionType::IN,
                        'date' => $inputs['date'],
                        'provider_id' => $package->provider_id,
                        'package_id' => $package->id,
                        'booking_id' => 0,
                        'created_by' => auth()->user()->id,
                        'created_at' => now(),
                    ];
                    if ($outstanding > $amount) {
                        $tmp['amount'] = $amount;
                        $amount = 0;
                        $draft_transactions[] = $tmp;
                        $package->payment_status = Status::PAYMENTPARTIAL;
                        $package->save();
                        break;
                    }
                    $amount -= $outstanding;
                    $package->payment_status = Status::PAYMENTCOMPLETED;
                    $package->save();
                    Booking::where('package_id', $package->id)->update(['payment_status' => Status::PAYMENTCOMPLETED]);
                    $draft_transactions[] = $tmp;
                }
            }

            MaTransaction::insert($draft_transactions);

            DB::commit();
            return $this->getSuccessResponse('success', 'Transaction added successfully');
        } catch (\Exception $exception) {
            DB::rollBack();
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }
}
