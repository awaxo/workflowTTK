<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Routing\Router;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception)) {
            // Excluding the stack trace
            $logMessage = sprintf(
                "[%s] %s: %s in %s:%d",
                now()->toDateTimeString(),
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );

            $logMessage .= PHP_EOL . "--------------------------------";

            Log::error($logMessage);
        }

        // Uncomment this line to log the stack trace
        parent::report($exception);
    }

    public function render($request, Throwable $e)
    {
        $e = $this->mapException($e);

        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $e = $this->prepareException($e);

        // Custom handling for CSRF TokenMismatchException
        if ($e instanceof TokenMismatchException) {
            return redirect()->route('login')->with('message', 'Your session has expired. Please login again.');
        }

        if ($response = $this->renderViaCallbacks($request, $e)) {
            return $response;
        }

        return match (true) {
            $e instanceof HttpResponseException => $e->getResponse(),
            $e instanceof AuthenticationException => $this->unauthenticated($request, $e),
            $e instanceof ValidationException => $this->convertValidationExceptionToResponse($e, $request),
            default => $this->renderExceptionResponse($request, $e),
        };
    }
}
