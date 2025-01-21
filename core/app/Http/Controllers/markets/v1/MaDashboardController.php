<?php

namespace App\Http\Controllers\markets\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\AreaService;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\VeServices;
use App\Models\WorkingSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\DayTrait;
use App\Traits\ResponseTrait;
use Carbon\Carbon;

class MaDashboardController extends Controller {
    use DayTrait, ResponseTrait;

    public function wizard(Request $request) {
        try {
            $data = $request->validate([
                'services' => ['required', 'array'],
                'services.*.id' => ['required', 'integer', 'exists:ve_services,id'],
                'services.*.base_price' => ['required', 'numeric'],
                'services.*.coverage' => ['array'],
                'services.*.coverage.*.city_id' => ['integer', 'exists:cities,id'],
                'services.*.coverage.*.areas' => ['array'],
                'services.*.coverage.*.areas.*' => ['integer', 'exists:areas,id'],
                //                'services.*.ranges' => ['required', 'array'],
                //                'services.*.ranges.*.from' => ['required', 'integer'],
                //                'services.*.ranges.*.to' => ['required', 'integer'],
                //                'services.*.ranges.*.price' => ['required', 'numeric'],
                'services.*.pricing_model_id' => ['required', 'integer', 'exists:pricing_models,id'],
                'services.*.cost_per_service' => ['required', 'numeric'],
                'services.*.markup' => ['required', 'numeric'],
                'services.*.duration' => ['required', 'numeric'],
                'services.*.visible' => ['boolean'],
                'services.*.capacity' => ['required', 'integer'],
                'services.*.capacity_threshold' => ['required', 'integer'],
                'schedule' => ['required', 'array'],
                'schedule.*.day' => ['required', 'string', Rule::in(['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])],
                'schedule.*.off' => ['required', 'boolean'],
                'schedule.*.from' => ['required_if:schedule.*.off,false', 'numeric'],
                'schedule.*.to' => ['required_if:schedule.*.off,false', 'numeric', 'gt:schedule.*.from'],
            ]);
            Db::beginTransaction();
            Setting::updateOrCreate([
                'key' => 'wizard_data',
            ], [
                'value' => json_encode($data),
            ]);
            $service_location = VeServices::where('id', $data['services'][0]['id'])->pluck('service_location')->first();
            // update service row with data
            foreach ($data['services'] as $serviceData) {
                $service = VeServices::withTrashed()->findOrFail($serviceData['id']);
                $service->restore();
                $service->update([
                    'pricing_model_id' => $serviceData['pricing_model_id'],
                    'cost' => $serviceData['cost_per_service'],
                    'capacity' => $serviceData['capacity'],
                    'capacity_threshold' => $serviceData['capacity_threshold'],
                    'markup' => $serviceData['markup'],
                    'visible' => $serviceData['visible'],
                    'duration' => $serviceData['duration'],
                    'status' => 1,
                    'updated_at' => now(),
                ]);
                // insert data to table area_service contain service ID and area ID only
                if ($service_location == 1) {
                    foreach ($serviceData['coverage'] as $coverageData) {
                        foreach ($coverageData['areas'] as $area) {
                            AreaService::updateOrCreate(
                                [
                                    'area_id' => $area, 'service_id' => $serviceData['id']
                                ],
                                [
                                    'area_id' => $area,
                                    'service_id' => $serviceData['id'],
                                    'status' => 0,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                        }
                    }
                }
                //                foreach ($serviceData['ranges'] as $range) {
                //                    PricingModelData::insert([
                //                        'pricing_models_id' => $serviceData['pricing_model_id'],
                ////                        'min' => $range['from'],
                ////                        'max' => $range['to'],
                //                        'additional_cost' => $serviceData['cost_per_service'],
                //                        'price' => $range['price'],
                //                        'created_at' => now(),
                //                        'updated_at' => now(),
                //                    ]);
                //                    // delete old data using service Id ,, pricing model Id
                //                    $check = PricingModelData::where('pricing_models_id', $serviceData['pricing_model_id'])
                //                        ->where('service_id', $serviceData['id'])
                //                        ->first();
                //                    if ($check)
                //                    {
                //                        $check->delete();
                //                    }
                //                    PricingModelData::insert([
                //                       'pricing_models_id' => $serviceData['pricing_model_id'],
                //                       'service_id' => $serviceData['id'],
                //                       'min' => $range['from'],
                //                       'max' => $range['to'],
                //                        'additional_cost' => $serviceData['cost_per_service'],
                //                        'price' => $range['price'],
                //                        'created_at' => now(),
                //                        'updated_at' => now(),
                //                    ]);
                //                }
            }
            foreach ($data['schedule'] as $scheduleData) {
                if (!$scheduleData['off']) {
                    WorkingSchedule::create([
                        'day' => $this->getDayId($scheduleData['day']),
                        'from' => $scheduleData['from'] ?? null,
                        'to' => $scheduleData['to'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            Setting::UpdateOrCreate([
                'key' => 'wizard_status',
            ], [
                'value' => json_encode(false),
            ]);
            DB::commit();
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage(), 500);
        }
    }

    public function get_status(Request $request) {
        try {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'status' => json_decode(Setting::where('key', 'wizard_status')->first()->value),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function get_additional_info() {
        try {
            //$data = Setting::where('key', 'additional_info')->first()->value;
            $data = DB::table('setting')
                ->select('value')->where('key', 'additional_info')->first();

            $data = json_decode($data->value);
            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function get_setting() {
        try {
            $data = DB::table('setting')
                ->select('value')->where('key', 'setting')->first();
            if (!empty($data)) {
                $data = json_decode($data->value);
            }
            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function getRevenue(Request $request) {
        $data = [];

        $locale = app()->getLocale();
        $titleField = $locale == 'ar' ? 'expense_categories.title_ar' : 'expense_categories.title_en';

        $bookingsData = Booking::selectRaw('DATE_PART(\'year\', date) as year, DATE_PART(\'month\', date) as month, COUNT(*) as total_bookings, SUM(total) as total_amount, payment_status')
            ->where('status', Status::BOOKINGCOMPLETED);

        $transactionsData = Transaction::selectRaw('DATE_PART(\'year\', date) as year, DATE_PART(\'month\', date) as month, type, SUM(amount) as total_amount');

        $expensesData = Expense::selectRaw("expenses.category_id, $titleField as category_title, expense_categories.color, SUM(expenses.amount) as total_amount")
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id');

        $this->applyBookingFilters($bookingsData, $request);
        $this->applyTransactionFilters($transactionsData, $request);
        $this->applyExpensesFilters($expensesData, $request);

        $bookingsData = $bookingsData
            ->groupBy(DB::raw('DATE_PART(\'year\', date)'), DB::raw('DATE_PART(\'month\', date)'), 'payment_status')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $transactionsData = $transactionsData
            ->groupBy(DB::raw('DATE_PART(\'year\', date)'), DB::raw('DATE_PART(\'month\', date)'), 'type')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $expensesData = $expensesData
            ->groupBy('expenses.category_id', 'category_title', 'expense_categories.color')
            ->get();

        $data['bookings'] = $bookingsData;
        $data['transactions'] = $transactionsData;
        $data['expenses'] = $expensesData;

        return $this->getSuccessResponse(__('retrieved_successfully'), $data);
    }

    private function applyTransactionFilters($query, $request) {
        $query->when($request->has('start'), function ($q) use ($request) {
            $startDate = $request->input('start');

            $q->when($request->has('end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('end');
                $q->whereBetween(DB::raw('DATE(date)'), [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->where(DB::raw('DATE(date)'), '>=', $startDate);
            });
        });
    }

    private function applyExpensesFilters($query, $request) {
        $query->when($request->has('start'), function ($q) use ($request) {
            $startDate = $request->input('start');

            $q->when($request->has('end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('end');
                $q->whereBetween(DB::raw('DATE(date)'), [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->where(DB::raw('DATE(date)'), '>=', $startDate);
            });
        });
    }

    private function applyBookingFilters($query, $request) {
        $query->when($request->has('start'), function ($q) use ($request) {
            $startDate = $request->input('start');

            $q->when($request->has('end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('end');
                $q->whereBetween(DB::raw('DATE(booking.date)'), [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->where(DB::raw('DATE(booking.date)'), '>=', $startDate);
            });
        });

        $query->when($request->has('customer_id'), function ($q) use ($request) {
            $q->where('customer_id', $request->input('customer_id'));
        });

        $query->when($request->has('provider_id'), function ($q) use ($request) {
            $providerId = $request->input('provider_id');
            $q->whereHas('providers', function ($query) use ($providerId) {
                $query->where('providers.id', $providerId);
            });
        });

        $query->when($request->has('services'), function ($q) use ($request) {
            $serviceIds = explode(',', $request->input('services'));
            $q->whereHas('services', function ($qs) use ($serviceIds) {
                $qs->whereIn('service_id', $serviceIds);
            });
        });
    }
}
