<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

class ValidationErrorsToFlasher
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $errors = $request->session()->get('errors');

        if ($errors instanceof ViewErrorBag && $errors->any()) {
            foreach ($errors->all() as $error) {
                flash()->error($error);
            }
        }

        return $response;
    }
}
