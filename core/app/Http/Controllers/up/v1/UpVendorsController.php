<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\up\ServiceResource;
use App\Http\Resources\VendorResource;
use App\Models\Domains;
use App\Models\Vendors;
use App\Models\VeServices;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Traits\TenantTrait;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class UpVendorsController extends Controller {
    use ResponseTrait, TenantTrait;

    public function index() {
        try {
            $data = Vendors::paginate(10);
            $data->data = VendorResource::collection($data);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function show(Request $request) {
        $validatedData = $request->validate([
            'id' => 'required|numeric|exists:vendors,id',
        ]);
        if (!$validatedData) {
            return $this->getErrorResponse('error', 'invalid data');
        }
        $vendor = Vendors::where('vendors.id', $validatedData['id'])->first();
        $vendor = new VendorResource($vendor);
        return $this->getSuccessResponse('success', $vendor);
    }


    public function get_areas_covered(Request $request) {
        try {
            $validatedData = $request->validate([
                'id' => 'required|numeric|exists:vendors,id',
            ]);
            $vendor = Vendors::find($validatedData['id']);
            if (!$vendor) {
                return $this->getErrorResponse('error', __('vendor_not_found'));
            }
            $tenant = $vendor->domain->tenant;
            $services = $tenant->run(function () use ($request) {
                $services = VeServices::all();
                $data = ServiceResource::collection($services);
                return $data->toArray($request);
            });
            return $this->getSuccessResponse('success', $services);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function get_vendor_offdays(Request $request) {
        try {
            $validatedData = $request->validate([
                'id' => 'required|numeric|exists:vendors,id',
            ]);
            if (!$validatedData) {
                return $this->getErrorResponse('error', 'invalid data');
            }
            $vendor = Vendors::find($validatedData['id']);
            if (!$vendor) {
                return $this->getErrorResponse('error', 'vendor not found');
            }
            $off_days = $this->switch_tenant($request->id, 'off_days')
                ->where('provider_id', null)->get();
            return $this->getSuccessResponse('success', $off_days);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function get_vendor_working_schedule(Request $request) {
        try {
            $validatedData = $request->validate([
                'id' => 'required|numeric|exists:vendors,id',
            ]);
            $vendor = Vendors::find($validatedData['id']);
            if (!$vendor) {
                return $this->getErrorResponse('error', 'vendor not found');
            }
            $working_schedule = $this->switch_tenant($request->id, 'working_schedule')
                ->where('provider_id', null)->get();
            return $this->getSuccessResponse('success', $working_schedule);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function get_vendor_statistics(Request $request) {
        try {
            $validatedData = $request->validate([
                'id' => 'required|numeric|exists:vendors,id',
            ]);
            $vendor = Vendors::find($validatedData['id']);
            if (!$vendor) {
                return $this->getErrorResponse('error', 'vendor not found');
            }
            $booking_count = $this->switch_tenant($vendor->id, 'booking')->count();
            $customer_count = $this->switch_tenant($vendor->id, 'customers')->count();
            $provider_count = $this->switch_tenant($vendor->id, 'providers')->count();
            $service_count = $this->switch_tenant($vendor->id, 've_services')->where('status', 1)->whereNull('deleted_at')->count();
            return $this->getSuccessResponse('success', [
                'booking_count' => $booking_count,
                'customer_count' => $customer_count,
                'provider_count' => $provider_count,
                'service_count' => $service_count,
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function getSubdomain(Request $request) {
        try {
            $request->validate([
                'org_name_en' => 'required|string|max:50',
            ]);

            $orgName = $request->input('org_name_en');
            $baseSubdomain = Str::slug($orgName, '-');

            // Truncate the subdomain to 30 characters
            $baseSubdomain = Str::limit($baseSubdomain, 30, '');

            $uniqueSubdomain = $baseSubdomain;
            $counter = 1;

            while ($this->isSubdomainTaken($uniqueSubdomain)) {
                $uniqueSubdomain = Str::limit($baseSubdomain, 28, '') . '-' . $counter;
                $counter++;
            }

            return $this->getSuccessResponse('success, domain available', [
                'subdomain' => $uniqueSubdomain,
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    /**
     * Check if a subdomain is taken or reserved.
     *
     * @param  string  $subdomain
     * @return bool
     */
    protected function isSubdomainTaken(string $subdomain): bool {
        $reservedSubdomains = config('subdomains.reserved', []);

        if (in_array($subdomain, $reservedSubdomains)) {
            return true;
        }

        return Domain::where('domain', $subdomain)->exists();
    }

    // run tenant migration using Artisan commands
    public function edit(Request $request) {
        try {
            // validate request first
            Validator::validate($request->all(), [
                'id' => 'required|numeric|exists:vendors,id',
            ]);
            $status = $request->input('status', false) ? 1 : 0;
            $vendor = Vendors::find($request->id);
            if (!$vendor) {
                return $this->getErrorResponse('error', 'vendor not found');
            }
            $vendor->org_name_en = $request->org_name_en ?? $vendor->org_name_en;
            $vendor->org_name_ar = $request->org_name_ar ?? $vendor->org_name_ar;
            $vendor->status = $status;
            $vendor->save();
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->getErrorResponse($th);
        }
    }

    public function update_subscription(Request $request) {
        $vendor = Vendors::findOrFail($request->id);
        $subscription = $vendor->lastSubscription();
        if ($request->action === 'renew' && !empty($subscription->plan->periodicity_type)) {
            $subscription->renew();
        }
        if ($request->action === 'cancel') {
            $subscription->suppress();
        }
        return $this->getSuccessResponse(__('updated-successfully'), $vendor);
    }
}
