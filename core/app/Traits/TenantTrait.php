<?php

namespace App\Traits;

use App\Models\Domains;
use App\Models\Tenant;
use App\Models\Vendors;
use Illuminate\Support\Facades\DB;

trait TenantTrait {
    protected function get_tenant_database($vendor_id) {
        $domain = Domains::where('vendor_id', $vendor_id)->first();
        if ($domain) {
            $tenant = Tenant::where('id', $domain->tenant_id)->first();
            if ($tenant) {
                return $tenant->tenancy_db_name;
            }
        }
        return null;
    }

    public function switch_tenant($vendor_id, $tb) {
        $db = $this->get_tenant_database($vendor_id);
        config(['database.connections.tenant.database' => $db]);
        return DB::connection('tenant')->table($tb);
    }

    public function get_tenant_id($user_id, $tb) {
        $vendor_id = Vendors::where('user_id', $user_id)->first()->id;
        return $this->switch_tenant($vendor_id, $tb);
    }
}
