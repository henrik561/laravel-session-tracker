<?php

namespace HenrikHannewijk\SessionTracker\Middleware;

use Closure;
use HenrikHannewijk\SessionTracker\SessionTrackerFacade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request as UserRequest;
use Illuminate\Support\Facades\Log as Logger;

class SessionTracker
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\UserRequest  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(UserRequest $request, Closure $next)
	{
		if (!Auth::check()) {
			if ($request->ajax()) {
				return response('Unauthorized.', 401);
			} else {
				SessionTrackerFacade::endSession(true);
				Auth::guard('web')->logout();
				$request->session()->invalidate();
				$request->session()->regenerateToken();
				return redirect()->route('login');
			}
		} else {
			if (SessionTrackerFacade::isSessionBlocked() || SessionTrackerFacade::isSessionInactive()) {
				if ($request->ajax()) {
					return response('Unauthorized.', 401);
				} else {
					Auth::guard('web')->logout();
					$request->session()->invalidate();
					$request->session()->regenerateToken();
					return redirect()->route('login');
				}
			} elseif (SessionTrackerFacade::isSessionLocked()) {
				return redirect()->route(Config::get('sessionTracker.security_code_route_name'));
			}
		}

		SessionTrackerFacade::refreshSession($request);
		SessionTrackerFacade::logSession($request);

		return $next($request);
	}
}
