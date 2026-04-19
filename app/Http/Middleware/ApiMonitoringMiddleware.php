<?php

namespace App\Http\Middleware;

use App\Models\UserVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;
class ApiMonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only monitor API requests
        if ($request->is('api/*')) {
            $this->logVisit($request);
        }

        return $response;
    }

    private function logVisit(Request $request): void
    {
        try {
            $agent = new Agent();
            $agent->setUserAgent($request->userAgent());

            UserVisit::create([
                'user_id' => $request->user()?->id,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device' => $this->getDevice($agent),
                'platform' => $agent->platform(),
                'browser' => $agent->browser(),
                'referer' => $request->header('referer'),
            ]);
        } catch (\Exception $e) {
            Log::error('API Monitoring failed: ' . $e->getMessage());
        }
    }

    private function getDevice(Agent $agent): string
    {
        if ($agent->isDesktop()) {
            return 'Desktop';
        } elseif ($agent->isTablet()) {
            return 'Tablet';
        } elseif ($agent->isMobile()) {
            return 'Mobile';
        }
        return 'Unknown';
    }
}
