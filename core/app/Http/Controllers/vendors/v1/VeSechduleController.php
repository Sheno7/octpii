<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Models\OffDays;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VeSechduleController extends Controller {
    use ResponseTrait;

    public function list_offDays() {
        try {
            $data = OffDays::where('provider_id', null)->get();
            return $this->getSuccessResponse('success', $data);
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function add_offDays(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'from' => 'required|date|after_or_equal:today|unique:off_days,from',
                'to' => 'required|date|unique:off_days,to',
                'title' => 'required|string|max:20',
                'provider_id' => 'sometimes|exists:providers,id',
            ]);
            if ($validate->fails()) {
                return $this->getValidationErrorResponse('validation_error', $validate->errors());
            }
            $off_day = OffDays::where('from', $request->from)
                // ->where('to', $request->to)
                ->where('provider_id', null)->first();
            if ($off_day) {
                $off_day->delete();
            }
            OffDays::create([
                'from' => $request->from,
                'to' => $request->to,
                'title' => $request->title,
                'provider_id' => $request->provider_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $matcher = new VeMatcherController();
            $matcher->offdaysAdd(OffDays::orderBy('id', 'desc')->first()->id);
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function edit_offDays(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'id' => 'required|exists:off_days,id',
                'from' => 'required|date|after_or_equal:today',
                'to' => 'required|date',
                'title' => 'required|string|max:20'
            ]);
            if ($validate->fails()) {
                return $this->getValidationErrorResponse('validation_error', $validate->errors());
            }
            $matcher = new VeMatcherController();
            $matcher->offdaysDelete($request->id);
            $off_days = OffDays::where('id', $request->id)->exists();
            if (!$off_days) {
                return $this->getErrorResponse('error', 'not found');
            }
            // update
            OffDays::where('id', $request->id)->update([
                'from' => $request->from,
                'to' => $request->to,
                'title' => $request->title,
                'provider_id' => null,
                'updated_at' => now()
            ]);
            $matcher->offdaysAdd($request->id);
            return $this->getSuccessResponse('success');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }

    public function delete_offDays(Request $request) {
        try {
            $validate = Validator::make($request->all(), [
                'id' => 'required|exists:off_days,id'
            ]);
            if ($validate->fails()) {
                return $this->getErrorResponse('validation_error', $validate->errors());
            }
            $off_days = OffDays::where('id', $request->id)->exists();
            if (!$off_days) {
                return $this->getErrorResponse('error', 'not found');
            }
            // id for offdays
            $off_days = OffDays::where('id', $request->id)->first()->id;
            $matcher = new VeMatcherController();
            $matcher->offdaysDelete($off_days);
            OffDays::where('id', $request->id)->delete();
            return $this->getSuccessResponse('success', 'deleted');
        } catch (\Exception $e) {
            return $this->getErrorResponse('error', $e->getMessage());
        }
    }
}
