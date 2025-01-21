<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Models\Areas;
use App\Models\Vendors;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Traits\TenantTrait;
use Illuminate\Support\Facades\Log as Logger;

class UpAreasController extends Controller
{
    use ResponseTrait , TenantTrait;

    public function index()
    {
        try {
            $data = Areas::join('cities', 'areas.city_id', '=', 'cities.id')
                ->select('areas.id', 'areas.title_ar', 'areas.title_en', 'areas.city_id', 'areas.lat', 'areas.long',
                    'cities.title_en AS city', 'areas.created_at')
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
                'lat' => 'required',
                'long' => 'required',
                'city_id' => 'required | numeric | exists:cities,id',
            ]);
            //save
            $areas = new Areas();
            $areas->title_ar = $request->title_ar;
            $areas->title_en = $request->title_en;
            $areas->lat = $request->lat;
            $areas->long = $request->long;
            $areas->city_id = $request->city_id;
            $areas->status = $request->status ?? 0;
            $areas->created_at = now();
            $areas->updated_at = now();
            $areas->upid = $areas->id;
            $areas->save();
            $this->trigger($areas);
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
                'id' => 'required | numeric | exists:areas,id',
            ]);
            //update
            $areas = Areas::findorfail($request->id);
            if (!$areas) {
                return $this->getErrorResponse('error', 'not found');
            }
            $areas->title_ar = $request->title_ar ?? $areas->title_ar;
            $areas->title_en = $request->title_en ?? $areas->title_en;
            $areas->city_id = $request->city_id ?? $areas->city_id;
            $areas->lat = $request->lat ?? $areas->lat;
            $areas->long = $request->long ?? $areas->long;
            $areas->status = $request->status ?? $areas->status;
            $areas->updated_at = now();
            $areas->save();
            $this->trigger($areas);
            return $this->getSuccessResponse('success', 'updated');
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
                'id' => 'required|integer|exists:areas,id',
            ]);
            $areas = Areas::find($request->id);
            if (!$areas) {
                return $this->getErrorResponse('error', 'not found');
            }
            $areas->delete();
            $this->trigger($areas);
            return $this->getSuccessResponse('success', 'deleted');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    protected function trigger($areas)
    {
        try {
            $vendors = Vendors::where('area_id', $areas->id)->get();
            foreach ($vendors as $vendor) {
                $this->switch_tenant($vendor->id, 'areas')
                    ->where('id', $areas->id)->updateOrInsert(
                        ['id' => $areas->id],
                        [
                            'title_ar' => $areas->title_ar,
                            'title_en' => $areas->title_en,
                            'lat' => $areas->lat,
                            'long' => $areas->long,
                            'city_id' => $areas->city_id,
                            'status' => $areas->status,
                           // 'updated_at' => now(),
                        ]
                    );
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
