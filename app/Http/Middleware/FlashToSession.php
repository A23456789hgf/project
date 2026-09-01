<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bridges PHPFlasher → standard Laravel session flash keys.
 *
 * Every controller calling flash()->success('msg') writes an envelope into
 * PHPFlasher's own session bag. This middleware reads those envelopes and
 * also writes them into the standard session('success') / session('error') /
 * session('warning') / session('info') keys so the Blade toastr snippet in
 * resources/views/layouts/app.blade.php can display them on every page.
 */
class FlashToSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! app()->bound('flasher.storage_manager')) {
            return $response;
        }

        try {
            $envelopes = app('flasher.storage_manager')->all();

            foreach ($envelopes as $envelope) {
                $notification = $envelope->getNotification();
                $type = $notification->getType();
                $message = $notification->getMessage();

                if ($type && $message && in_array($type, ['success', 'error', 'warning', 'info'], true)) {
                    session()->flash($type, $message);
                }
            }

            if (! empty($envelopes)) {
                app('flasher.storage_manager')->clear();
            }
        } catch (\Throwable $e) {
            // Never break the response.
        }

        return $response;
    }
}
