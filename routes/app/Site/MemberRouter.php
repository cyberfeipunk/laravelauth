<?php
namespace Routes\App\Site;

use Illuminate\Contracts\Routing\Registrar;

class MemberRouter{
    function map(Registrar $router){
        $router->get('/members',"MemberController@members");
        $router->get('/members/login',"MemberController@login");
        $router->get('/members/logout',"MemberController@logout");
        $router->get('/members/{id}',"MemberController@show");
    }
}