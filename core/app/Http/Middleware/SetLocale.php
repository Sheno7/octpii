<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale {
    public function handle($request, Closure $next) {
        $supportedLocales = ['en', 'ar'];
        $locale = $request->header('Accept-Language');

        if ($locale && in_array($locale, $supportedLocales)) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
