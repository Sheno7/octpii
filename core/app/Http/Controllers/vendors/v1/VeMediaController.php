<?php

namespace App\Http\Controllers\vendors\v1;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Validator;

class VeMediaController extends Controller {

    use ResponseTrait;

    public function index() {
        try {
            $data = DB::table('media')->get();
            $response = $this->getSuccessResponse('success', $data);
            return $this->getSuccessResponse('success', $response);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }

    public function upload($file, $title, $model_type, $model_id) {
        try {
            $file_name = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/media/' . $model_type), $file_name);
            $media = new Media();
            $media->file = $file_name;
            $media->title = $title;
            $media->model_type = $model_type;
            $media->model_id = $model_id;
            $media->save();
            return $this->getSuccessResponse('success', $media);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }

    public function getMediaByModelId($model_type, $model_id) {
        try {
            //           $data =  Media::select('id', 'title' ,'file')->where('model_type', $model_type)->where('model_id', $model_id)->get();
            //            return $this->getSuccessResponse('success', $data);
            return  Media::select('id', 'title', 'file')
                ->where('model_type', $model_type)
                ->where('model_id', $model_id)
                ->get();
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }

    public function deleteMedia($id) {
        try {
            $media = Media::find($id);
            $media->delete();
            $file_path = public_path('uploads/media/' . $media->model_type . '/' . $media->file);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            return $this->getSuccessResponse('success', 'deleted successfully');
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse($th->getMessage());
        }
    }
}
