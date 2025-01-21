<?php

namespace App\Http\Controllers\up\v1;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Resources\up\ServiceResource;
use App\Models\Sectors;
use App\Models\Services;
use App\Models\Vendors;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Traits\TenantTrait;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class UpServicesController extends Controller {
    use ResponseTrait, TenantTrait;

    public function index() {
        try {
            $services = Services::orderBy('id', 'desc')->paginate(10);
            $services->data = ServiceResource::collection($services);
            return $this->getSuccessResponse('success', $services);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function add(Request $request) {
        try {
            // validation
            $validator = Validator::make($request->all(), [
                'title_ar' => 'required',
                'title_en' => 'required',
                'description_ar' => 'required',
                'description_en' => 'required',
                'category_id' => 'required|numeric|exists:service_categories,id',
                'sector_id' => 'required|numeric|exists:sectors,id',
                'service_location' => 'required|numeric|between:0,1',
                'icon' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->getValidationErrorResponse('error', $validator->errors());
            }

            // add service to DB
            $service = new Services();
            $service->title_ar = $request->title_ar;
            $service->title_en = $request->title_en;
            $service->description_ar = $request->description_ar;
            $service->description_en = $request->description_en;
            $service->sector_id = $request->sector_id;
            $service->status = $request->status ? $request->status : 0;
            $service->service_location = $request->service_location;
            $service->created_at = now();
            $service->updated_at = now();
            $service->upid = $service->id;
            $service->category_id = $request->category_id;
            //$service->icon = $request->icon;
            // upload icon
            if (request()->hasFile('icon')) {
                $iconFile = request()->file('icon');
                $iconFileName = time() . '.' . $iconFile->getClientOriginalExtension();
                $iconFile->move(public_path('uploads/service'), $iconFileName);
                $service->icon = url('uploads/service/' . $iconFileName);
            }
            $service->save();
            $this->trigger($service);
            return $this->getSuccessResponse('success', $service);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }


    public function edit(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'sector_id' => 'numeric | exists:sectors,id',
                'id' => 'required | numeric | exists:services,id',
            ]);

            if ($validator->fails()) {
                return $this->getErrorResponse('error', $validator->errors());
            }
            $service = Services::findOrfail($request->id);

            $service->title_ar = $request->title_ar ?? $service->title_ar;
            $service->title_en = $request->title_en ?? $service->title_en;
            $service->description_ar = $request->description_ar ?? $service->description_ar;
            $service->description_en = $request->description_en ?? $service->description_en;
            $service->sector_id = $request->sector_id ?? $service->sector_id;
            $service->category_id = $request->category_id ?? $service->category_id;
            $service->service_location = $request->service_location ?? $service->service_location;
            $service->status = $request->status ?? $service->status;
            if (request()->hasFile('icon')) {
                $old = parse_url($service->icon, PHP_URL_PATH);
                if (file_exists(public_path('uploads/service/' . $old)) && $old) {
                    unlink(public_path($old));
                }
                $iconFile = request()->file('icon');
                $iconFileName = time() . '.' . $iconFile->getClientOriginalExtension();
                $iconFile->move(public_path('uploads/service'), $iconFileName);
                $service->icon = url('uploads/service/' . $iconFileName);
            }
            $service->updated_at = now();
            $this->count();
            $this->trigger($service);
            $service->save();
            return $this->getSuccessResponse('success', $service);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
    public function destroy(Request $request) {
        try {
            $service = Services::find($request->id);
            if ($service) {
                $service->delete();
                return $this->getSuccessResponse('success');
            } else {
                return $this->getErrorResponse('error', 'Service not found');
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    protected function count() {
        try {

            $sectors = Sectors::where('status', Status::ACTIVE)->get();
            foreach ($sectors as $sector) {
                $services = Services::where('sector_id', $sector->id)->where('status', 1)->count();
                if ($services === 0) {
                    $sector->status = Status::INACTIVE;
                    $sector->save();
                }
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
    protected function trigger($service) {
        try {
            // get active vendors
            $vendors = Vendors::where('status', Status::ACTIVE)->where('sector_id', $service->sector_id)->get();
            foreach ($vendors as $vendor) {
                // will sync only with same
                // switch to tenant database
                $this->switch_tenant($vendor->id, 've_services')
                    ->where('upid', $service->upid)->updateOrInsert(
                        ['upid' => $service->upid],
                        [
                            'title_ar' => $service->title_ar,
                            'title_en' => $service->title_en,
                            'description_ar' => $service->description_ar,
                            'description_en' => $service->description_en,
                            'sector_id' => $service->sector_id,
                            'status' => $service->status,
                            //                            'created_at' => $service->created_at,
                            //                            'updated_at' => $service->updated_at,
                        ]
                    );
            }
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
