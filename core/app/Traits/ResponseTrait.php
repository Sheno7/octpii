<?php

namespace App\Traits;

trait ResponseTrait {
    public function getSuccessResponse($message, $data = null) {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => [
                'response' => $data ?? 'success'
            ],
        ]);
    }

    public function getSuccessWithoutDataResponse($data = null) {
        return response()->json([
            'status' => true,
            'response' => $data ?? 'success'
        ]);
    }

    public function getSuccessResponseEmpty($message) {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => [
                'response' => 'success'
            ],
        ]);
    }

    public function getErrorResponse($message, $data = null, $status = 500) {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $data,
            'data' => [
                'response' => $data,
            ],
        ], $status);
    }

    public function getErrorResponseNotFount($message, $data = null, $status = 404) {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => [
                'response' => $data,
            ],
        ], $status);
    }

    public function getErrorValidationResponse($message, $data = null) {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ]);
    }


    public function getValidationErrorResponse($message, $data = null, $status = 422) {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $data,
            'data' => $data,
        ], $status);
    }
}
