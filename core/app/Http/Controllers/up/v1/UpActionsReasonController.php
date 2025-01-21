<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Models\ActionReason;
use App\Models\Areas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;

class UpActionsReasonController extends Controller
{
    public function index()
    {
        try {
            $data = ActionReason::join('sectors', 'action_reason.sector_id', '=', 'sectors.id')
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
            $request->validate([
                'action' => 'required',
                'reason' => 'required | text',
                'status' => 'numeric | between:0,1',
                'sector_id' => 'required | numeric | exists:sectors,id',
            ]);
            $action_reason = new ActionReason();
            $action_reason->action = $request->action;
            $action_reason->reason = $request->reason;
            $action_reason->status = $request->status;
            $action_reason->sector_id = $request->sector_id;
            $action_reason->save();
            return $this->getSuccessResponse('success', $action_reason);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }

    }

    public function edit(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required | numeric | exists:action_reasons,id',
                'action' => 'required',
                'reason' => 'required | text',
                'status' => 'numeric | between:0,1',
                'sector_id' => 'required | numeric | exists:sectors,id',
            ]);
            $action_reason = ActionReason::find($request->id);
            $action_reason->action = $request->action ?? $action_reason->action;
            $action_reason->reason = $request->reason ?? $action_reason->reason;
            $action_reason->status = $request->status ?? $action_reason->status;
            $action_reason->sector_id = $request->sector_id ?? $action_reason->sector_id;
            $action_reason->save();
            return $this->getSuccessResponse('success', $action_reason);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required | numeric | exists:action_reasons,id',
            ]);
            $action_reason = ActionReason::find($request->id);
            $action_reason->delete();
            return $this->getSuccessResponse('success', $action_reason);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }
}
