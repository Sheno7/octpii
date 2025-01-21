<?php

namespace App\Http\Controllers\vendors\v1;

use App\Enums\Status;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProcurementRequest;
use App\Http\Requests\UpdateProcurementRequest;
use App\Http\Resources\ProcurementResource;
use App\Models\Procurement;
use App\Models\Transaction;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;

class VeProcurementController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $procurements = Procurement::with(['transaction', 'product'])->paginate(10);
        $procurements->data = ProcurementResource::collection($procurements);
        return $this->getSuccessResponse(__('retrieved_successfully'), $procurements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProcurementRequest $request) {
        try {
            $inputs = $request->validated();
            $inputs['branch_id'] = $request->get('branch_id', $request->selected_branch);
            DB::beginTransaction();
            $transaction = Transaction::create([
                'amount' => $inputs['price'],
                'status' => Status::PAYMENTCOMPLETED,
                'payment_method_id' => $inputs['payment_method_id'],
                'type' => TransactionType::OUT,
                'date' => $inputs['date'],
                'created_by' => auth()->user()->id,
            ]);
            $inputs['transaction_id'] = $transaction->id;
            $procurement = Procurement::create($inputs);
            $product = $procurement->product;
            $product->stock += $procurement->quantity;
            $product->save();
            DB::commit();
            return $this->getSuccessResponse(__('stored_successfully'), $procurement);
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
        $procurement = Procurement::findOrFail($id);
        return $this->getSuccessResponse(__('retrieved_successfully'), $procurement);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProcurementRequest $request) {
        $inputs = $request->validated();
        $procurement = Procurement::findOrFail($inputs['id']);
        $updated_quantity = $inputs['quantity'] - $procurement->quantity;
        $procurement->update($inputs);
        $procurement->transaction->update([
            'amount' => $inputs['price'],
            'payment_method_id' => $inputs['payment_method_id'],
            'date' => $inputs['date'],
        ]);
        $product = $procurement->product;
        $product->stock += $updated_quantity;
        $product->save();
        return $this->getSuccessResponse(__('updated_successfully'), $procurement);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy() {
        //
    }
}
