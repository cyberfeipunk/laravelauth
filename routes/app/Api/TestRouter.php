<?php
namespace Routes\App\Api;


use Illuminate\Contracts\Routing\Registrar;

class TestRouter {

    public function map(Registrar $router){
        $router->get('test',"TestController@test");
    }
}