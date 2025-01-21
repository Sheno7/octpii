<?php

namespace App\Exceptions;

use GuzzleHttp\Client;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        $client = new Client();
        $body = [
            'appName' => $request->getHttpHost(),
            // 'type'=>config('app.url'),
            'dirFromRoot' => $e->getFile() ?: 'Empty dirFromRoot',
            'callFrom' => $request->getPathInfo() ?: 'Empty callFrom',
            'exception' => $e->getMessage() ?: 'Empty Exception',
            'data' => (object)$request->all(),
        ];

        try {
            $client->request('POST', env('EXCEPTION_URL'), [
                'connect_timeout' => 2,
                'body' => json_encode($body, JSON_PRESERVE_ZERO_FRACTION),
                'headers' => [
                    "content-type" => "application/json"
                ]
            ]);
            // }
        } catch (\Exception $ex) {
            return parent::render($request, $e);
        }
        return parent::render($request, $e);
    }
    //sentry
    public function report(Throwable $exception)
    {

    }
}
