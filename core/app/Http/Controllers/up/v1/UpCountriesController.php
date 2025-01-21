<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Models\Countries;
use App\Models\Vendors;
use App\Traits\TenantTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;

class UpCountriesController extends Controller
{
    use TenantTrait, ResponseTrait;

    public function index()
    {
        try {
            $data = Countries::select('id', 'title_ar', 'title_en', 'isocode', 'flag', 'code', 'created_at')
                ->orderBy('updated_at', 'desc')
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
                'isocode' => 'required',
                'flag' => 'required',
                'code' => 'required | numeric',
                'currency' => 'required',
                // 'status' => 'numeric | between:0,1'
            ]);
            //store
            $country = new Countries();
            $country->title_ar = $request->title_ar;
            $country->title_en = $request->title_en;
            $country->isocode = $request->isocode;
            $country->flag = $request->flag;
            $country->code = $request->code;
            $country->currency = $request->currency;
            $country->status = $request->status ?? 0;
            // update upid in countries table after insert with return id
            $country->upid = $country->id;
            $country->save();
            $this->trigger($country);
            return $this->getSuccessResponse('success');
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
                'title_ar' => 'required',
                'title_en' => 'required',
//                'isocode' => 'required',
//                'flag' => 'required',
//                'code' => 'required',
                'status' => 'required'
            ]);
            //update
            $country = Countries::findorfail($request->id);
            if (!$country) {
                return $this->getErrorResponse('error', 'not found');
            }
            $country->title_ar = $request->title_ar;
            $country->title_en = $request->title_en;
            $country->isocode = $request->isocode;
            $country->flag = $request->flag;
            $country->code = $request->code;
            $country->save();
            $this->trigger($country);
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function show(Request $request)
    {
        try {
           $data = Countries::select('title_ar', 'title_en', 'isocode', 'flag', 'code', 'created_at')
               ->where('id', $request->id)
               ->get();
           return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            $country = Countries::findorfail($request->id);
            $country->delete();
            $this->trigger($country);
            return $this->getSuccessResponse('success');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    // trgigger updates from master database to tenant database
    protected function trigger($country)
    {
        try {
            // get active vendors
            $vendors = Vendors::where('status', 1)->get();
            foreach ($vendors as $vendor) {
                // switch to tenant database
                $this->switch_tenant($vendor->id, 'countries')
                    ->where('upid', $country->upid)->updateOrInsert(
                        ['upid' => $country->upid],
                        [
                            'title_ar' => $country->title_ar,
                            'title_en' => $country->title_en,
                            'isocode' => $country->isocode,
                            'flag' => $country->flag,
                            'code' => $country->code,
                            'currency' => $country->currency,
                            'status' => $country->status,
                          //  'updated_at' => now(),
                        ]
                    );
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
