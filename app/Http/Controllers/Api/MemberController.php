<?php

namespace App\Http\Controllers\Api;

use App\Models\MemberAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class MemberController extends Controller
{
    //

    //
    public function __construct()
    {
        $this->middleware('auth:membertoken')->only('show');
    }

    public function members(){
        //请求header 中增加 Authorization 值 为"Bearer 123456"
        $guard = Auth::guard('membertoken');
        if($guard->check()){
            return "已经登录";
        }else{
            return "未登录";
        }
//        $account = new MemberAccount();
//        $account =  MemberAccount::where(['account_token'=>'123456'])->first();
//        $a = ";";
//        return $account;

    }

    public function show(Member $member){
        $guard = Auth::guard('membertoken');
        return $guard->user();
    }

    public function login(){
        $credentials = ['login_account'=>'cyberpunk','login_password'=>'123456'];
        $remember = false;
        //$password = bcrypt('123456');
        $guard=Auth::guard('membertoken');
        $loginResult = $guard->attempt($credentials,$remember);
        return 1;
    }

    public function logout(){
        $guard=Auth::guard('membertoken');
        $logout_result = $guard->logout();
        return 1;
    }

}
