<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpParser\Builder\Method;

class TestController extends Controller
{
    //
    function test(){
        return __METHOD__;
    }
}
