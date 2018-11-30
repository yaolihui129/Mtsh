<?php
namespace Jinruihs\Controller;
class TestController extends BaseController
{
    public function index()
    {
        $appID=C(appID);
        dump('appID:'.$appID);
        $ticket=S($appID.'ticket');
        dump('ticket:'.$ticket);
        $access_token= S($appID.'access_token');
        dump('access_token:'.$access_token);

        $this->display();
    }


  

}