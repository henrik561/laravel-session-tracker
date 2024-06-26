<?php

namespace HenrikHannewijk\SessionTracker;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class SessionTrackerServiceProvider extends ServiceProvider
{

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			base_path('vendor/henrik561/laravel-session-tracker/src/config/config.php') => config_path('sessionTracker.php'),
		], 'henrik561-session-tracker-config');

		$this->publishes([
			base_path('vendor/henrik561/laravel-session-tracker/src/migrations') => base_path('database/migrations')
		], 'henrik561-session-tracker-migrations');

		$router = $this->app['router'];
		$router->middleware('session.tracker', 'HenrikHannewijk\SessionTracker\Middleware\SessionTracker');
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			base_path('vendor/henrik561/laravel-session-tracker/src/config/config.php'),
			'sessionTracker'
		);
		$this->registerSessionTracker();
		$this->registerAuthenticationEventHandler();
	}

	/**
	 * Register the the sessionTracker facade.
	 *
	 * @return void
	 */
	private function registerSessionTracker()
	{
		$this->app->bind('sessionTracker', function ($app) {
			return new SessionTracker($app);
		});
	}

	private function registerAuthenticationEventHandler()
	{

		Event::subscribe('HenrikHannewijk\SessionTracker\AuthenticationHandler');
	}
}
