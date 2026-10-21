<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Closes the public site when maintenance mode is on.
 * The admin panel and logged-in admins are never blocked.
 */
class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('x9k2m7p4', 'x9k2m7p4/*', 'livewire/*')) {
            return $next($request);
        }

        if (auth()->check() && auth()->user()->hasRole('admin')) {
            return $next($request);
        }

        if (Setting::get('maintenance', 'enabled')) {
            $locale = app()->getLocale() === 'en' ? 'en' : 'ar';

            return response()->view('maintenance', [
                'title' => Setting::get('maintenance', "title_{$locale}", 'الموقع قيد الصيانة'),
                'message' => Setting::get('maintenance', "message_{$locale}", 'نعتذر، الموقع تحت الصيانة حالياً.'),
                'locale' => $locale,
            ], 503);
        }

        return $next($request);
    }
}
