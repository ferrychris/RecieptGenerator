<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessExists
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->business_id) {
            // Re-flash any pending notification (e.g. the "logged in" toast
            // from registration) so it survives this extra redirect hop —
            // session flash data only lives for one hop by default.
            return redirect()->route('business.create')
                ->with($request->session()->only(['success', 'error', 'message']));
        }

        return $next($request);
    }
}
