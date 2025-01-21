<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Models\Cities;
use App\Models\Vendors;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Traits\TenantTrait;
use Illuminate\Support\Facades\Log as Logger;

class UpCitiesController extends Controller
{
    use ResponseTrait, TenantTrait;

    public function index()
    {
        try {
            $data = Cities::select('cities.id', 'cities.title_ar', 'cities.title_en',
                'countries.title_en AS country', 'cities.created_at','countries.id as country_id')
                ->join('countries', 'cities.country_id', '=', 'countries.id')
                ->orderBy('id', 'desc')
                ->paginate(10);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function add(Request $request)
    {
        try {
            //validation
            $request->validate([
                'title_ar' => 'required',
                'title_en' => 'required',
                'country_id' => 'required | numeric | exists:countries,id',
                'status' => 'numeric|between:0,1'
            ]);
            //save
            $cities = new Cities();
            $cities->title_ar = $request->title_ar;
            $cities->title_en = $request->title_en;
            $cities->country_id = $request->country_id;
            $cities->status = $request->status ?? 0;
            $cities->updated_at = now();
            $cities->created_at = now();
            $cities->upid = $cities->id;
            $cities->save();
            $this->trigger($cities);
            return $this->getSuccessResponse('success', $cities);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function edit(Request $request)
    {
        try {
            //validation
            $request->validate([
                'id' => 'required | numeric | exists:cities,id',
            ]);
            //update
            $cities = Cities::findorfail($request->id);
            if (!$cities) {
                return $this->getErrorResponseNotFount('error', 'not found');
            }
            $cities->title_ar = $request->title_ar ?? $cities->title_ar;
            $cities->title_en = $request->title_en ?? $cities->title_en;
            $cities->country_id = $request->country_id ?? $cities->country_id;
            $cities->status = $request->status ?? $cities->status;
            $cities->save();
            $this->trigger($cities);
            return $this->getSuccessResponse('success', $cities);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            // validation request
            $request->validate([
                'id' => 'required | numeric | exists:cities,id',
            ]);
            $cities = Cities::findorfail($request->id);
            if (!$cities) {
                return $this->getErrorResponse('error');
            }
            $cities->delete();
            // delete all areas related to this city
            $cities->areas()->delete();
            $this->trigger($cities);
            return $this->getSuccessResponse('success', 'deleted');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    protected function trigger($cities)
    {
        try {
            // get active vendors
            $vendors = Vendors::where('status', 1)->get();
            foreach ($vendors as $vendor) {
                // switch to tenant database
                $this->switch_tenant($vendor->id, 'cities')
                    ->where('upid', $cities->upid)->updateOrInsert(
                        ['upid' => $cities->upid],
                        [
                            'title_ar' => $cities->title_ar,
                            'title_en' => $cities->title_en,
                            'country_id' => $cities->country_id,
                            'status' => $cities->status,
//                            'created_at' => $cities->created_at,
//                            'updated_at' => $cities->updated_at,
                        ]
                    );
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
