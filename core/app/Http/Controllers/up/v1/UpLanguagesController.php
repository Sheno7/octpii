<?php

namespace App\Http\Controllers\up\v1;

use App\Http\Controllers\Controller;
use App\Models\Domains;
use App\Models\Languages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as Logger;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\File;

class UpLanguagesController extends Controller {
    use ResponseTrait;
    public function index() {
        try {
            $data = Languages::select('key', 'value')->paginate(10);
            return $this->getSuccessResponse('success', $data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    public function getLanguageById(Request $request) {
        try {
            $request->validate([
                'sub_domain' => 'required|string',
            ]);
            $sub_domain = str_replace('.octopii.cloud', '', $request->sub_domain);
            $domain = Domains::where('domain', 'LIKE', "%{$sub_domain}%")->first();
            if (!$domain) {
                return $this->getErrorResponse('Domain not found', null, 404);
            }
            $sector_id = $this->getSectorByDomain($sub_domain);
            $data = Languages::select('value')->where('id', $sector_id)->first()?->value;

            if (!$data) {
                $data = [];
                $arabic = resource_path('lang/ar.json');
                $english = resource_path('lang/en.json');

                if (File::exists($arabic)) {
                    $data['ar'] = json_decode(File::get($arabic));
                }
                if (File::exists($english)) {
                    $data['en'] = json_decode(File::get($english));
                }

                if (count($data) === 0) {
                    return $this->getErrorResponse('error', 'Language not found', 404);
                }
            }
            return $this->getSuccessWithoutDataResponse($data);
        } catch (\Throwable $th) {
            Logger::error($th->getMessage());
            return $this->getErrorResponse('error', $th->getMessage());
        }
    }

    protected function getSectorByDomain($domain) {
        $domain = Domains::where('domain', 'LIKE', "%{$domain}%")->first();
        $business = $domain->vendor ?? $domain->market;
        return $business?->sectors->pluck('id')->first();
    }
}
