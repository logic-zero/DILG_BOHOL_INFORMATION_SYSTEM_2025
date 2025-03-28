<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PageVisit;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TrackPageVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        // Exclude authenticated users (e.g., admin pages)
        if (auth()->check()) {
            return $next($request);
        }

        $ip = $request->ip();

        // Check if a visit from this IP was recorded in the last 10 seconds
        $recentVisit = PageVisit::where('ip_address', $ip)
            ->where('created_at', '>=', Carbon::now()->subSeconds(10))
            ->exists();

        if (!$recentVisit) {
            PageVisit::create(['ip_address' => $ip]);

            // Clear cached count to refresh visit count
            Cache::forget('page_visit_count');
        }

        return $next($request);
    }
}
