<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Models\Markets;
use App\Models\MaVendor;
use App\Models\Sectors;
use App\Models\Services;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class VeMarketController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of available marketplaces.
     */
    public function index() {
        $vendor = tenant()->domains->first()->vendor;
        $sectors = $vendor->sectors->pluck('id');
        $markets = Markets::whereHas('sectors', function ($q) use ($sectors) {
            $q->whereIn('sectors.id', $sectors);
        })->where('status', 1)->paginate(10);

        // TODO: Add joined:boolean

        return $this->getSuccessResponse(__('retrieved_successfully'), $markets);
    }

    /**
     * Request to be linked to a marketplace
     */
    public function add(Request $request) {
        $market_id = $request->get('market_id');
        $vendor = tenant()->domains->first()->vendor;
        $sectors = $vendor->sectors->pluck('id');
        $market = Markets::findOrFail($market_id);
        if ($market->sectors->whereIn('id', $sectors)->count() === 0) {
            return $this->getErrorResponse(__('invalid_market'));
        }
        $current_vendor = [
            'vendor_up_id' => tenant()->domains->first()->vendor_id,
            'tenant_id' => tenant()->id,
        ];
        $market_tenant = $market->domain->tenant;
        $result = $market_tenant->run(function () use ($current_vendor, $sectors) {
            $maSectors = Sectors::whereIn('upid', $sectors)->get();
            $vendor = MaVendor::firstOrCreate($current_vendor, []);
            $vendor->sectors()->sync($maSectors);
            return $vendor;
        });

        return $this->getSuccessResponse(__('added_successfully'), $result);
    }

    /**
     * List a marketplace services
     */
    public function listServices(Request $request) {
        $market_id = $request->get('market_id');
        $vendor = tenant()->domains->first()->vendor;
        $sectors = $vendor->sectors->pluck('id');
        $market = Markets::findOrFail($market_id);
        if ($market->sectors->whereIn('id', $sectors)->count() === 0) {
            return $this->getErrorResponse(__('invalid_market'));
        }
        $market_tenant = $market->domain->tenant;

        $services = $market_tenant->run(function () use ($sectors) {
            return Services::whereHas('category', function ($q) use ($sectors) {
                $q->whereHas('sector', function ($q) use ($sectors) {
                    $q->whereIn('sectors.upid', $sectors);
                });
            })->get();
        });

        return $this->getSuccessResponse(__('retrieved_successfully'), $services);
    }

    /**
     * Link vendor services to a marketplace services
     */
    public function linkServices(Request $request) {
        $market_id = $request->get('market_id');
        $vendor = tenant()->domains->first()->vendor;
        $sectors = $vendor->sectors->pluck('id');
        $market = Markets::findOrFail($market_id);
        if ($market->sectors->whereIn('id', $sectors)->count() === 0) {
            return $this->getErrorResponse(__('invalid_market'));
        }
        $servicesArray = $request->get('services');
        $current_vendor = [
            'vendor_up_id' => tenant()->domains->first()->vendor_id,
            'tenant_id' => tenant()->id,
        ];
        $market_tenant = $market->domain->tenant;
        $result = $market_tenant->run(function () use ($current_vendor, $servicesArray) {
            $vendor = MaVendor::where($current_vendor)->first();
            $vendor->services()->sync($servicesArray);
            return $vendor->services;
        });

        return $this->getSuccessResponse(__('added_successfully'), $result);
    }

    /**
     * List the providers linked to a marketplace
     */
    public function listProviders(Request $request) {
        $market_id = $request->get('market_id');
        $market = Markets::findOrFail($market_id);
        $current_vendor = [
            'vendor_up_id' => tenant()->domains->first()->vendor_id,
            'tenant_id' => tenant()->id,
        ];
        $market_tenant = $market->domain->tenant;
        $result = $market_tenant->run(function () use ($current_vendor) {
            $vendor = MaVendor::where($current_vendor)->first();
            return $vendor->providers;
        });

        return $this->getSuccessResponse(__('retrieved_successfully'), $result);
    }

    /**
     * Link providers to a marketplace
     */
    public function linkProviders(Request $request) {
        $market_id = $request->get('market_id');
        $market = Markets::findOrFail($market_id);
        $providersArray = $request->get('providers');
        $current_vendor = [
            'vendor_up_id' => tenant()->domains->first()->vendor_id,
            'tenant_id' => tenant()->id,
        ];
        $market_tenant = $market->domain->tenant;
        $result = $market_tenant->run(function () use ($current_vendor, $providersArray) {
            $vendor = MaVendor::where($current_vendor)->first();
            foreach ($providersArray as $provider) {
                $vendor->providers()->firstOrCreate($provider);
            }
            return $vendor->providers;
        });

        return $this->getSuccessResponse(__('added_successfully'), $result);
    }
}
