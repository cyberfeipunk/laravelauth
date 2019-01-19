<?php
namespace Routes\App\Site;

use Illuminate\Contracts\Routing\Registrar;

class TestRoute{

    public function map(Registrar $router){
        $router->get('test','TestController@test');
    }
}
?>