<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class MobileNotificationController extends Controller {
    use ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $user = auth()->user();
        $notifications = $user->mobileNotifications()->paginate(10);

        $user->mobileNotifications()
            ->whereIn('id', $notifications->pluck('id'))
            ->update(['is_read' => true, 'read_at' => now()]);

        return $this->getSuccessResponse(__('retrieved-successfully'), $notifications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }
}
