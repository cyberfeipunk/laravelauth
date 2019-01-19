<?php

namespace App\Providers;

use App\Http\Auth\EloquentMemberProvider;
use App\Http\Auth\MemberSessionGuard;
use App\Http\Auth\MemberTokenGuard;
use Illuminate\Support\ServiceProvider;
use Auth;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Auth::extend('membersessionguard',function($app, $name, $config){

            $guard_config =$this->app['config']['auth.guards.'.$name];
            $provider_config = $this->app['config']['auth.providers.'.$guard_config['provider']];
            $provider = new EloquentMemberProvider($app['hash'], $provider_config['model']);
            $guard = new MemberSessionGuard($name, $provider, $this->app['session.store']);
            // When using the remember me functionality of the authentication services we
            // will need to be set the encryption instance of the guard, which allows
            // secure, encrypted cookie values to get generated for those cookies.
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($this->app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($this->app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
            }
            return $guard;
        });

        Auth::extend('membertokenguard', function($app,$name,$config){
            // The token guard implements a basic API token based guard implementation
            // that takes an API token field from the request and matches it to the
            // user in the database or another persistence layer where users are.
            $guard_config =$this->app['config']['auth.guards.'.$name];
            $provider_config = $this->app['config']['auth.providers.'.$guard_config['provider']];
            $provider = new EloquentMemberProvider($app['hash'], $provider_config['model']);
            $guard = new MemberTokenGuard($provider, $this->app['request']);
            $this->app->refresh('request', $guard, 'setRequest');
            return $guard;
        });

        Auth::extend("eloqumentmemberprovider",function(){
           return new EloquentMemberProvider();
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
