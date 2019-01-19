<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
class MemberController extends Controller
{

    public function __construct(){
        $this->middleware("auth:membersession")->only('show');
    }
    //
    public function members(){
        $guard = Auth::guard('membersession');
        if($guard->check()){
            return "已经登录";
        }else{
            return "未登录";
        }
    }

    public function show(){
        return __METHOD__;
    }

    public function login(){
        $credentials = ['login_account'=>'cyberpunk','login_password'=>'123456'];
        $remember = false;
        $password = bcrypt('123456');
        $guard=Auth::guard('membersession');
        $loginResult = $guard->attempt($credentials,$remember);
        return 1;
    }

    public function logout(){
        $guard=Auth::guard('membersession');
        $logout_result = $guard->logout();
        return 1;
    }
}
