<?php

namespace App\Http\Controllers\vendors\v1;

use App\Enums\CommissionType;
use App\Enums\Status;
use App\Enums\TransactionType;
use App\Exports\ProviderFinancialExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\BookingProvider;
use App\Models\Providers;
use App\Models\ProvidersAction;
use App\Models\Transaction;
use App\Traits\ResponseTrait;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class VeFinancialController extends Controller {
    use ResponseTrait;

    public function index(Request $request) {
        try {
            $data = Providers::with(['user'])
                ->orderBy(function ($query) {
                    $query->selectRaw("CONCAT(users.first_name, ' ', users.last_name)")
                        ->from('users')
                        ->whereColumn('users.id', 'providers.user_id')
                        ->limit(1);
                }, 'asc');

            $data->when($request->has('phone'), function ($q) use ($request) {
                $phone = $request->input('phone');
                $q->whereHas('user', function ($data) use ($phone) {
                    $data->where('phone', $phone);
                });
            });

            $data = $data->paginate(10);
            // Transform each item in the paginated data
            $modifiedData = $data->getCollection()->map(function ($item) {
                $earning = $this->provider_earning($item);
                $all_received = $this->provider_received($item->id);
                $received = doubleval($all_received->sum('amount'));
                $outstanding = $earning['total'] - $received;
                return [
                    "provider_id" => $item->id,
                    "salary" => $item->salary,
                    "commission" => [
                        "type" => $item->commission_type,
                        "amount" => $item->commission_amount,
                    ],
                    "first_name" => $item->user->first_name,
                    "last_name" => $item->user->last_name,
                    "phone" => $item->user->phone,
                    "earning" => $earning,
                    "received" => $received,
                    "outstanding" => $outstanding,
                    "last_payment" => $all_received->first(),
                ];
            });

            // Replace the original collection with the modified one
            $data->setCollection($modifiedData);
            if ($request->get('export', false)) {
                return Excel::download(new ProviderFinancialExport($modifiedData), 'providers-financial.xlsx');
            }
            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }

    protected function provider_collected($provider_id) {
        try {
            return BookingProvider::where('provider_id', $provider_id)
                ->join('booking', 'booking.id', '=', 'booking_provider.booking_id')
                ->where('booking.status', 2)
                ->sum('booking.total');
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }

    protected function provider_earning($provider) {
        $commissions = 0;
        $commission_type = $provider->commission_type;
        $commission_amount = $provider->commission_amount;
        $salary = $provider->salary;

        $startedDate = Carbon::parse($provider->start_date);
        $currentDate = Carbon::now();
        $monthsDifference = $startedDate->diffInMonths($currentDate);

        if ($commission_amount > 0) {
            $commissions = $provider->bookings->where('status', Status::BOOKINGCOMPLETED)->reduce(function ($accumulator, $booking) use ($commission_type, $commission_amount) {
                $amount = $commission_amount;
                if ($commission_type === CommissionType::PERCENTAGE) {
                    $amount = $booking->total * $commission_amount / 100;
                }
                return $accumulator + $amount;
            }, 0);
        }

        $salaries = $salary * $monthsDifference;
        $total = $commissions + $salaries;

        return [
            'commissions' => $commissions,
            'salaries' => $salaries,
            'total' => $total
        ];
    }

    protected function provider_received($provider_id) {
        return ProvidersAction::where('provider_id', $provider_id)
            ->where('action', 1)
            ->orderBy('created_at', 'desc');
    }

    protected function provider_supply($provider_id) {
        return ProvidersAction::where('provider_id', $provider_id)
            ->where('action', 0)
            ->sum('amount');
    }

    public function add(Request $request) {
        try {
            DB::beginTransaction();
            $validate = Validator::make($request->all(), [
                'provider_id' => ['required', 'integer', 'exists:providers,id'],
                'amount' => ['required', 'numeric', 'min:1'],
                'type' => ['required', 'integer'],
                'payment_method_id' => ['required', 'exists:payment_method,id'],
                'date' => ['required', 'date']
            ]);
            if ($validate->fails()) {
                return $this->getValidationErrorResponse($validate->errors());
            }
            $transaction = new Transaction();
            $transaction->provider_id = $request->provider_id;
            $transaction->amount = $request->amount;
            $transaction->payment_method_id = $request->payment_method_id;
            $transaction->type = TransactionType::OUT;
            $transaction->date = $request->date;
            $transaction->status = Status::PAYMENTCOMPLETED;
            $transaction->created_by = auth()->user()->id;
            $transaction->created_at = Carbon::now();
            $transaction->updated_at = Carbon::now();
            $this->proccess_wallet($request->provider_id, $request->amount, $request->type);
            $transaction->save();
            $provider_action = new ProvidersAction();
            $provider_action->transaction_id = $transaction->id;
            $provider_action->provider_id = $request->provider_id;
            $provider_action->action = $request->type;
            $provider_action->amount = $request->amount;
            $provider_action->created_at = Carbon::now();
            $provider_action->updated_at = Carbon::now();
            $provider_action->save();
            DB::commit();
            return $this->getSuccessResponse('success');
        } catch (\Exception $exception) {
            DB::rollBack();
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }

    public function update_financial(Request $request) {
        try {
            $validate = Validator::make(
                $request->all(),
                [
                    'id' => ['required', 'integer', 'exists:transaction,id'],
                    'provider_id' =>
                    [
                        'required',
                        'integer',
                        'exists:providers,id',
                        Rule::exists('transaction')->where(function ($query) use ($request) {
                            $query->where('id', $request->id)
                                ->where('provider_id', $request->provider_id);
                        }),
                    ],
                    'amount' => ['required', 'numeric', 'min:1'],
                    'payment_method_id' => ['required', 'exists:payment_method,id'],
                    'type' => ['required', 'integer'],
                    'date' => ['required', 'date']
                ]
            );
            DB::beginTransaction();
            if ($validate->fails()) {
                return $this->getValidationErrorResponse($validate->errors());
            }
            $transaction = Transaction::find($request->id);
            if (!$transaction) {
                return $this->getErrorResponse('transaction not found');
            }

            if ($transaction->created_at->format('Y-m-d') != Carbon::now()->format('Y-m-d')) {
                return $this->getErrorResponse('you can not edit this transaction , transaction date is not today');
            }

            if ($transaction->created_by != auth()->user()->id) {
                return $this->getErrorResponse('you can not edit this transaction , transaction created by another user');
            }
            $transaction->provider_id = $request->input('provider_id', $transaction->provider_id);
            $transaction->payment_method_id = $request->payment_method_id;
            $transaction->amount = $request->input('amount', $transaction->amount);
            $transaction->type = TransactionType::OUT;
            $transaction->date = $request->input('date', $transaction->date);
            $transaction->created_by = auth()->user()->id;
            $transaction->updated_at = Carbon::now();
            $this->proccess_wallet($transaction->provider_id, $transaction->amount, $transaction->type);
            $transaction->save();
            $provider_action = ProvidersAction::where('transaction_id', $request->id)->first();
            if ($provider_action) {
                $provider_action->provider_id = $transaction->provider_id;
                $provider_action->amount = $transaction->amount;
                $provider_action->updated_at = Carbon::now();
                $provider_action->save();
            }
            DB::commit();
            return $this->getSuccessResponse('Transaction updated successfully.');
            return $this->getSuccessResponse('success');
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }

    protected function proccess_wallet($provider_id, $amount, $type) {
        try {
            DB::beginTransaction();
            $provider = Providers::findOrfail($provider_id);
            if ($type == 0) {
                $wallet = $provider->wallet - $amount;
            } elseif ($type == 1) {
                $wallet = $provider->wallet + $amount;
            }
            $provider->wallet = $wallet;
            $provider->updated_at = Carbon::now();
            $provider->save();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }

    public function provider_financial_list(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'provider_id' => ['required', 'integer', 'exists:providers,id'],
            ]);
            if ($validate->fails()) {
                return $this->getValidationErrorResponse($validate->errors());
            }

            $provider = Providers::findOrfail($request->provider_id);
            if (!$provider) {
                return $this->getErrorResponse('provider not found');
            }
            $data = Transaction::where('provider_id', $request->provider_id)
                ->orderby('created_at', 'desc')
                ->paginate(10);

            $data->data = TransactionResource::collection($data);

            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $exception) {
            Logger::error($exception->getMessage());
            return $this->getErrorResponse($exception->getMessage());
        }
    }
}
