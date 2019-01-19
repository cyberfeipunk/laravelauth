<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Member extends Model
{
    //
    var $primaryKey = "member_id";
    protected $fillable = ['name','mobile','email'];

}
