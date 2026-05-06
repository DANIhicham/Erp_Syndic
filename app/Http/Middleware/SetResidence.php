<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetResidence
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
        {
            if (!session('residence_id')) {
                $residence = \App\Models\Residence::first();

                if ($residence) {
                    session(['residence_id' => $residence->id]);
                }
            }

            return $next($request);
        }
}
