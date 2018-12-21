<?php
namespace Xinda\Controller;
class TestController extends BaseController
{
    public function index()
    {
        $appID=C(appID);
        dump('appID:'.$appID);
        $ticket=S($appID.'ticket');
        dump('ticket:'.$ticket);
        $access_token= $this->getAccessToken();
        dump('access_token:'.$access_token);
        $ip=GetIP();
        dump('IP:'.$ip);

        dump($_SERVER);

        $this->display();
    }


  

}