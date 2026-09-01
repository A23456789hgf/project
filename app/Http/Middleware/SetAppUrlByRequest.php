<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetAppUrlByRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // 🔵 داخل الشبكة (IP داخلي)
        if ($host === '10.172.172.9' || $host === 'localhost' || $host === '127.0.0.1') {
            config(['app.url' => 'http://'.$host.':8841']);
            config(['session.secure' => false]);
            URL::forceScheme('http');
        }
        // 🟢 خارج الشبكة (الدومين الرسمي)
        else {
            config(['app.url' => 'https://project.mafwr.gov.ye']);
            config(['session.secure' => true]);
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
