<?php

namespace HenrikHannewijk\SessionTracker\Middleware;

use Closure;
use HenrikHannewijk\SessionTracker\SessionTrackerFacade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SessionTracker
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (!Auth::check()) {
			if ($request->ajax()) {
				return response('Unauthorized.', 401);
			} else {
				SessionTrackerFacade::endSession(true);
				return redirect()->route(Config::get('sessionTracker.logout_route_name'));
			}
		} else {
			if (SessionTrackerFacade::isSessionBlocked() || SessionTrackerFacade::isSessionInactive()) {
				if ($request->ajax()) {
					return response('Unauthorized.', 401);
				} else {
					return redirect()->route(Config::get('sessionTracker.logout_route_name'));
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
