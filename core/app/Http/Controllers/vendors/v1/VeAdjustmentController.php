<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdjustmentRequest;
use App\Http\Requests\UpdateAdjustmentRequest;
use App\Http\Resources\AdjustmentResource;
use App\Models\Adjustment;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;

class VeAdjustmentController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $adjustments = Adjustment::with(['product'])->paginate(10);
        $adjustments->data = AdjustmentResource::collection($adjustments);
        return $this->getSuccessResponse(__('retrieved_successfully'), $adjustments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdjustmentRequest $request) {
        try {
            $inputs = $request->validated();
            $inputs['branch_id'] = $request->get('branch_id', $request->selected_branch);
            DB::beginTransaction();
            $adjustment = Adjustment::create($inputs);
            $product = $adjustment->product;
            $product->stock -= $adjustment->quantity;
            $product->save();
            DB::commit();
            return $this->getSuccessResponse(__('stored_successfully'), $adjustment);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->getErrorResponse(__('error'), $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show() {
        $id = request()->get('id');
        $adjustment = Adjustment::findOrFail($id);
        return $this->getSuccessResponse(__('retrieved_successfully'), $adjustment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdjustmentRequest $request) {
        $inputs = $request->validated();
        $adjustment = Adjustment::findOrFail($inputs['id']);
        $updated_quantity = $inputs['quantity'] - $adjustment->quantity;
        $adjustment->update($inputs);
        $product = $adjustment->product;
        $product->stock -= $updated_quantity;
        $product->save();
        return $this->getSuccessResponse(__('updated_successfully'), $adjustment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy() {
        //
    }
}
