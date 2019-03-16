Laravel 自定义auth认证方法步骤 自定义用户表及字段
关于laravel的认证原理网上有很多介绍，想要了解原理建议通过Xdebug不断调试扒原码来学习，

本文实现了使用自定义用户表，自定义用户表字段名，实现laravel的自定义认证，经过一翻学习和调试，我并没有严格按照网上介绍的原理方法一步一步自定义，而是抄近路直接继承和larave默认的User认证，本文代码中的model、guard和provider均继承自laravel的默认User认证，这样就只需要需要修改自定义内容就可以了，可以不断挖掘laravel默认认证的其它功能，并省去了很多方法重写的麻烦，另外laravel默认的认证方式除了登录认证还有很多其它功能，本文只测试了session和token登录及认证，其它功能没有测试。需要用到其它功能时可以参考默认User使用方法调用，然后再xdebug调试，不能使用默认的部分重写自定义方法就可以了。

github地址：https://github.com/cyberfeipunk/laravelauth

我的自定义认证表名pam_members，步骤如下

1，新建用户认证表Model   App/Models/PamMembers.php

复制代码
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

/***
继承自laravel默认认证方式的User Model的父类
 **/
class PamMembers extends User
{
    //
　　
　　protected $table = 'pam_members';
    var $primaryKey = ['member_id','login_type'];

    public $incrementing = false;

    /**
     * @return mixed|string
     * 密码字段
     */
    public function getAuthPassword()
    {
        return $this->login_password;       //password字段名
    }

    /**
     * @return string
     */
    public function getRememberTokenName(){
        return "login_account";  //token字段名称 此处原表没有token字段随便找了个字段测试用
    }

    /**
     * Get the name of the unique identifier for the user.
     * 返回用户标识字段
     */
    public function getAuthIdentifierName()
    {
        return "member_id";   //因为这里主键是混合的，所以需要配置一个session记录值字段名
    }

}
复制代码
 

2，新建Session Guard  /App/Http/Auth/SessionGuard.php

复制代码
<?php

namespace App\Http\Auth;

use Illuminate\Auth\SessionGuard;

/**
 * Class MemberSessionGuard
 * @package App\Http\Auth
 * 继承自laravel默认认证的SessionGurad
 */
class MemberSessionGuard extends SessionGuard
{
    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->loggedOut) {
            return;
        }

        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->getName());

        // First we will try to load the user using the identifier in the session if
        // one exists. Otherwise we will check for a "remember me" cookie in this
        // request, and if one exists, attempt to retrieve the user using that.
        if (! is_null($id) && $this->user = $this->provider->retrieveById($id)) {
            $this->fireAuthenticatedEvent($this->user);
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $recaller = $this->recaller();

        if (is_null($this->user) && ! is_null($recaller)) {
            $this->user = $this->userFromRecaller($recaller);

            if ($this->user) {
                $this->updateSession($this->user->getAuthIdentifier());

                $this->fireLoginEvent($this->user, true);
            }
        }

        return $this->user;
    }
}
复制代码
3,新建Token Guard    /App/Http/Auth/MemberTokenGuard.php

复制代码
<?php

namespace App\Http\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

/**
 * Class MemberTokenGuard
 * @package App\Http\Auth
 * 继承自laravel默认token认证
 */
class MemberTokenGuard extends TokenGuard
{
    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $inputKey
     * @param  string  $storageKey
     * @return void
　　　*   参数中$inputKey和$storageKey写为token字段名
     */
    public function __construct(UserProvider $provider, Request $request, $inputKey = 'login_account', $storageKey = 'login_account')
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = $inputKey;
        $this->storageKey = $storageKey;
    }

}
复制代码
4,新建Provider    /App/Http/Auth/EloquentMemberProvider.php

复制代码
<?php

namespace App\Http\Auth;

use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\EloquentUserProvider;

/**
 * Class EloquentMemberProvider
 * @package App\Http\Auth
 * 继承自laravel默认
 */
class EloquentMemberProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                array_key_exists('login_password', $credentials))) {
            return;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->createModel()->newQuery();

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'login_password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }


    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['login_password'];

        //return $this->hasher->check($plain, $user->getAuthPassword());
        return $this->checkMemberPassword($user,$plain);
    }

    //自定义表密码加密方式
    public function createMemberPassword($user,$password){
        $account = $user->password_account;
        $createtime = $user->createtime;
        $string_md5 = md5(md5($password).$account.$createtime);
        $front_string = substr($string_md5,0,31);
        $end_string = 's'.$front_string;
        return $end_string;
    }
    //密码验证
    public function checkMemberPassword($user,$passowrd){
        $enPassword = $this->createMemberPassword($user,$passowrd);
        if($user->login_password === $enPassword){
            return true;
        }else{
            return false;
        }
    }
}
复制代码
5,服务注册在/app/Providers/AppServiceProvider.php boot方法中加入如下代码

复制代码
/**
注意增加use列表

use App\Http\Auth\EloquentMemberProvider;
use App\Http\Auth\MemberTokenGuard;
use App\Http\Auth\MemberSessionGuard;
use Auth;*/
        //扩展 membersessionguard 认证方式
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
        //扩展membersessionguard认证方式
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

        //扩展认证方式的服务提供
        Auth::extend("eloqumentmemberprovider",function(){
            return new EloquentMemberProvider();
        });
复制代码
 

6，配置/config/auth.php

复制代码
/**
元素guards下增加如下子元素
*/

        'membersession' =>[
            'driver' => 'membersessionguard',  //AppServiceProvider中配置的名子
            'provider' => 'members'  //providers中的名子
        ],
        'membertoken' => [
            'driver' => 'membertokenguard',
            'provider' => 'members'
        ]

/*
元素providers增加如下子元素说明

*/
 'members' => [
            'driver' => 'eloqumentmemberprovider',  //AppServiceProvider中配置的服务名
            'model' => App\Models\PamMembers::class,
        ]
复制代码
 

7，session登录测试

复制代码
public function login(){
        $credentials = ['login_account'=>'cyberpunk','login_password'=>'123456'];
        $remember = false;
        $guard = Auth::guard('membersession');
        $loginResult = $guard->attempt($credentials,$remember);
        return $this->responseJson($loginResult);
    }
复制代码
8,session验证测试

复制代码
        $guard = Auth::guard('membersession');
        if($guard->check()){
            return "已经登录";
        }else{
           return '未登录';
        }
复制代码
9，token认证测试

复制代码
        /*
        * 测试请求header增加参数Authorization参数值为字符串 'Bearer '+token  注意中间有空格
        **/
        $guard = Auth::guard('membertoken');
        if($guard->check()){
           return '认证成功';
        }else{
           return '认证失败';
        }            
复制代码
 

 

 
