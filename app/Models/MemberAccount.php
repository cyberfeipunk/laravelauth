<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class MemberAccount extends User
{
    //

    protected $fillable = ['member_id','login_type','login_account','login_password','account_token','token_ip','token_expired'];

    var $primaryKey= "account_id";
    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->login_password;
    }

    public function getRememberTokenName(){
        return "account_token";
    }
}
