<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;

class VeExpenseController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $expenses = Expense::orderBy('id', 'desc');
        $this->applyFilters($expenses);
        $expenses = $expenses->paginate(10);
        $expenses->data = ExpenseResource::collection($expenses);
        return $this->getSuccessResponse(__('retrieved_successfully'), $expenses);
    }

    /**
     * Apply filter to the resource.
     */
    private function applyFilters($query) {
        $request = request();

        $query->when($request->has('id'), function ($q) use ($request) {
            $q->where('id', $request->input('id'));
        });

        $query->when($request->has('categories'), function ($q) use ($request) {
            $categories = explode(',', $request->input('categories', ''));
            $q->whereIn('category_id', $categories);
        });
        
        $query->when($request->has('sectors'), function ($q) use ($request) {
            $sectors = explode(',', $request->input('sectors', ''));
            $q->whereIn('sector_id', $sectors);
        });

        $query->when($request->has('expense_at_start'), function ($q) use ($request) {
            $startDate = $request->input('expense_at_start');

            $q->when($request->has('expense_at_end'), function ($q) use ($request, $startDate) {
                $endDate = $request->input('expense_at_end');
                $q->whereBetween(DB::raw('DATE(date)'), [$startDate, $endDate]);
            }, function ($q) use ($startDate) {
                $q->where(DB::raw('DATE(date)'), '>=', $startDate);
            });
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request) {
        $inputs = $request->validated();
        DB::beginTransaction();
        try {
            $inputs['created_by'] = $request->user()->id;
            $inputs['branch_id'] = $request->get('branch_id', $request->get('selected_branch', null));
            $expense = Expense::create($inputs);

            if ($request->hasFile('attachment')) {
                $media = new VeMediaController();
                $file = $request->file('attachment');
                $title = now();
                $model_type = 'expense';
                $model_id = $expense->id;
                $upload = $media->upload($file, $title, $model_type, $model_id);
            }

            DB::commit();

            return $this->getSuccessResponse(__('stored_successfully'), $expense);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return $this->getErrorResponse(__('server_error'), $th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show() {
        $id = request()->get('id');
        $expense = Expense::findOrFail($id);
        return $this->getSuccessResponse(__('retrieved_successfully'), $expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request) {
        $inputs = $request->validated();
        $expense = Expense::findOrFail($inputs['id']);
        DB::beginTransaction();
        try {
            if ($request->hasFile('attachment')) {
                $media = new VeMediaController();
                $attachments = $media->getMediaByModelId('expense', $inputs['id']);
                foreach ($attachments as $item) {
                    $media->deleteMedia($item['id']);
                }
                $file = $request->file('attachment');
                $title = now();
                $model_type = 'expense';
                $model_id = $expense->id;
                $uploadResponse = $media->upload($file, $title, $model_type, $model_id);
                $responseContent = $uploadResponse->getContent();
                $responseData = json_decode($responseContent, true);
                if ($responseData['status']) {
                    $inputs['attachment'] = $responseData['data']['response']['id'];
                }
            }
            $expense->update($inputs);
            DB::commit();
            return $this->getSuccessResponse(__('updated_successfully'), $expense);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->getErrorResponse(__('server_error'), $th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy() {
        $id = request()->get('id');
        $expense = Expense::findOrFail($id);
        DB::beginTransaction();
        try {
            if (!$expense->delete()) {
                return $this->getErrorResponse(__('cannot_delete'), $expense);
            }
            DB::commit();
            return $this->getSuccessResponse(__('deleted_successfully'), $expense);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->getErrorResponse(__('server_error'), $th);
        }
    }
}
