<?php

namespace App\Http\Controllers\markets\v1;

use App\Filters\Markets\Vendors\KeywordFilter;
use App\Filters\Markets\Vendors\SectorsFilter;
use App\Enums\CommissionType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\ProviderResource;
use App\Http\Resources\Customer\ServiceResource;
use App\Http\Resources\Customer\VendorResource;
use App\Models\MaVendor;
use App\Models\Providers;
use App\Models\ServiceVendor;
use App\Models\Tenant;
use App\Models\VendorProvider;
use App\Models\Vendors;
use App\Models\VeServices;
use App\Models\WorkingSchedule;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class MaVendorController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        try {
            $vendors = MaVendor::select('vendor_up_id')->distinct()->get();
            $ids=$vendors->pluck('vendor_up_id')->toArray();
               $query= app(Pipeline::class)
                    ->send(Vendors::whereIn('id', $ids))
                    ->through([
                        KeywordFilter::class,
                        SectorsFilter::class
                    ])
                ->thenReturn();
            return VendorResource::collection($query->simplePaginate());
        } catch (\Exception $e) {
            Logger::error($e->getMessage());
            return $this->getErrorResponse('An unexpected error occurred', $e->getMessage());
        }
    }

    /**
     * Apply filter to the resource.
     */
    private function applyFilters($query) {
        $request = request();

        $query->when($request->has('sectors'), function ($q) use ($request) {
            $sectors = explode(',', $request->input('sectors', ''));
            $q->whereIn('sector_id', $sectors);
        });

        $query->when($request->has('categories'), function ($q) use ($request) {
            $categories = explode(',', $request->input('categories', ''));
            $q->whereIn('category_id', $categories);
        });

        $query->when($request->has('sectors'), function ($q) use ($request) {
            $sectors = explode(',', $request->input('sectors', ''));
            $q->whereIn('sector_id', $sectors);
        });

        $query->when($request->has('expense_at_start'), function ($q) use ($request) {
            $startDate = $request->input('expense_at_start');

            $q->when($request->has('expense_at_end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('expense_at_end');
                $q->whereBetween(DB::raw('DATE(date)'), [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->where(DB::raw('DATE(date)'), '>=', $startDate);
            });
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $user = auth()->user();
        $vendor = MaVendor::findOrFail($id);
        if (!empty($user)) {
            $this->addRecentlyViewed($user->id, $id);
        }
        $tenant = Tenant::find($vendor->tenant_id);
        $vendor->upVendor->id = $id;
        $data['vendor'] = new VendorResource($vendor->upVendor);
        $veServiceIds = ServiceVendor::where('vendor_id', $id)->where('status', 1)->pluck('ve_service_id')->toArray();
        $other_services = $tenant->run(function () use ($veServiceIds) {
            return VeServices::with('category.sector')->whereIn('id', $veServiceIds)->orderBy('id', 'ASC')->get();
        });
        $data['services'] = ServiceResource::collection($other_services);

        $veProvidersIds = VendorProvider::where('vendor_id', $id)->where('status', 1)->pluck('provider_id')->toArray();
        $providers = $tenant->run(function () use ($veProvidersIds) {
            return Providers::with('services', 'areas', 'user')->whereIn('id', $veProvidersIds)->orderBy('id', 'ASC')->get();
        });
        $data['providers'] = ProviderResource::collection($providers);

        $workingHours = $tenant->run(function () {
            return WorkingSchedule::whereNull('provider_id')->orderBy('id', 'ASC')->get();
        });
        $data['working_hours'] = $workingHours;

        $data['reviews'] = [];

        return $this->getSuccessResponse('Vendor retrieved successfully', $data);
    }

    /**
     * List favorites vendors
     */
    public function listFavorites() {
        $user = request()->user();
        $vendors = $user->favoriteVendors;
        $data = [];

        foreach ($vendors as $vendor) {
            $data[] = [
                "id" => $vendor->id,
                "org_name" => app()->getLocale() === 'ar' ? $vendor->upVendor->org_name_ar : $vendor->upVendor->org_name_en,
                "description" => app()->getLocale() === 'ar' ? $vendor->upVendor->description_ar : $vendor->upVendor->description_en,
                "image" => $vendor->upVendor->image,
                "sectors" => $vendor->upVendor->sectors->map(function ($sector) {
                    return [
                        'id' => $sector->id,
                        'title' => app()->getLocale() === 'ar' ? $sector->title_ar : $sector->title_en,
                    ];
                }),
            ];
        }
        $data = $this->paginate($data);
        return $this->getSuccessResponse('Vendors retrieved successfully', $data);
    }

    /**
     * Toggle vendor to favorites
     */
    public function toggleFavorites() {
        $user = request()->user();
        $id = request()->get('vendor_id');
        $vendor = MaVendor::findOrFail($id);
        $user->favoriteVendors()->toggle([$vendor->id]);
        return $this->getSuccessResponse('success');
    }

    /**
     * List UP vendors
     */
    public function listUp() {
        $vendors = Vendors::all();
        return $this->getSuccessResponse(__('retrieved-successfully'), $vendors);
    }

    /**
     * List all vendors
     */
    public function listAll() {
        $vendors = MaVendor::all();
        return $this->getSuccessResponse(__('retrieved-successfully'), $vendors);
    }

    /**
     * Store Vendor details
     */
    public function store(Request $request) {
        $upVendor = Vendors::findOrFail($request->get('vendor_up_id'));
        $vendor = MaVendor::updateOrCreate([
            'vendor_up_id' => $request->get('vendor_up_id'),
        ], [
            'tenant_id' => $upVendor->domain->tenant_id,
            'status' => $request->get('status', false),
            'commission_type' => $request->get('commission_type', CommissionType::FIXED),
            'commission_amount' => $request->get('commission_amount', 0),
        ]);
        return $this->getSuccessResponse(__('saved-successfully'), $vendor);
    }

    /**
     * Update Vendor details
     */
    public function update(Request $request, $id) {
        $vendor = MaVendor::findOrFail($id);
        $vendor->status = $request->get('status', false);
        $vendor->commission_type = $request->get('commission_type', CommissionType::FIXED);
        $vendor->commission_amount = $request->get('commission_amount', 0);
        $vendor->save();
        return $this->getSuccessResponse(__('saved-successfully'), $vendor);
    }

    /**
     * Update Vendor service details
     */
    public function updateServiceDetails(Request $request, $id) {
        $vendor = MaVendor::findOrFail($request->get('vendor_id'));
        $service = $vendor->services()->wherePivot('ve_service_id', $request->get('ve_service_id'))->first();
        if ($service) {
            $vendor->services()->updateExistingPivot($service->id, [
                'status' => $request->get('status', false)
            ]);
            return $this->getSuccessResponse(__('saved-successfully'));
        }
        return $this->getErrorResponse(__('not-found'), null, 404);
    }

    /**
     * Display the specified resource.
     */
    public function getVendorDetails(string $id) {
        $vendor = MaVendor::with(['services'])->findOrFail($id);
        $tenant = Tenant::find($vendor->tenant_id);
        $vendor->upVendor->id = $id;
        $data['vendor'] = $vendor;
        $veServiceIds = ServiceVendor::where('vendor_id', $id)->where('status', 1)->pluck('ve_service_id')->toArray();
        $other_services = $tenant->run(function () use ($veServiceIds) {
            return VeServices::with('category.sector')->whereIn('id', $veServiceIds)->orderBy('id', 'ASC')->get();
        });
        $data['services'] = ServiceResource::collection($other_services);

        $veProvidersIds = VendorProvider::where('vendor_id', $id)->where('status', 1)->pluck('provider_id')->toArray();
        $providers = $tenant->run(function () use ($veProvidersIds) {
            return Providers::with('services', 'areas', 'user')->whereIn('id', $veProvidersIds)->orderBy('id', 'ASC')->get();
        });
        $data['providers'] = ProviderResource::collection($providers);

        $workingHours = $tenant->run(function () {
            return WorkingSchedule::whereNull('provider_id')->orderBy('id', 'ASC')->get();
        });
        $data['working_hours'] = $workingHours;

        $data['reviews'] = [];

        return $this->getSuccessResponse('Vendor retrieved successfully', $data);
    }

    private function getRecentlyViewed($user_id) {
        return DB::table('recently_viewed_vendors')
            ->where('user_id', $user_id)
            ->orderBy('viewed_at', 'desc')
            ->limit(20)
            ->get();
    }

    private function addRecentlyViewed($user_id, $vendor_id) {
        DB::table('recently_viewed_vendors')->updateOrInsert(
            [
                'user_id' => $user_id,
                'vendor_id' => $vendor_id,
            ],
            [
                'updated_at' => now(),
                'viewed_at' => now(),
            ]
        );

        // Clean up old entries if exceeding limit
        $limit = 20;
        // Get IDs of entries to delete
        $entriesToDelete = DB::table('recently_viewed_vendors')
            ->where('user_id', $user_id)
            ->orderBy('viewed_at', 'desc')
            ->skip($limit)
            ->pluck('id'); // Get IDs to delete

        // Delete entries by IDs
        DB::table('recently_viewed_vendors')
            ->whereIn('id', $entriesToDelete)
            ->delete();
    }
}
