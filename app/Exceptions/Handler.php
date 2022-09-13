<?php

namespace App\Exceptions;

use App\Enums\Globals\ResponseParam;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
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
        /*
        $this->renderable(function (NotFoundHttpException $e, $request) {

            return response()->json(["message" => 404], 404);
        });
        */
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {

        $guard = $exception->guards()[0];
        if ($request->expectsJson()) {
            return response()->json([
                ResponseParam::status()->key => 'A01',
                ResponseParam::msg()->key =>  'Unauthenticated',
            ], 401);
        }

        switch ($guard) {
            case 'sanctum':
            case 'cms-api':
                return response()->json([
                    ResponseParam::status()->key => 'T01',
                    ResponseParam::msg()->key =>  $exception->getMessage(),
                ], 401);
                break;
            default:
                return redirect(Route('cms.login'));
        }
    }

}
