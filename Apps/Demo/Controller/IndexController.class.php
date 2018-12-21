<?php
namespace Demo\Controller;
class IndexController extends BaseController
{
    public function index()
    {
        $website=getWebsite();
        $website='http://127.0.0.1';
        $data=array(
            array(
                'moudle'=>$website.'/Lagou',
                'img'=>'https://ss0.bdstatic.com/-0U0bnSm1A5BphGlnYG/tam-ogel/b921bee784a1290f22e3b5d47c710d95_121_121.jpg',
                'name'=>'拉钩',
                'admin'=>$website.'/Lagou/Admin',
            ),
            array(
                'moudle'=>$website.'/dd_shop',
                'img'=>'https://ss0.bdstatic.com/-0U0bnSm1A5BphGlnYG/tam-ogel/a47e8e7511848ef42591444be726c7c3_121_121.jpg',
                'name'=>'当当网',
                'admin'=>$website.'/dd_shop/Admin',
            ),
            array(
                'moudle'=>$website.'/dis800',
                'img'=>'https://ss2.baidu.com/6ONYsjip0QIZ8tyhnq/it/u=3425060465,3616892087&fm=58&bpow=220&bpoh=220',
                'name'=>'折800',
                'admin'=>$website.'/dis800/Admin',
            ),
            array(
                'moudle'=>$website.'/guomei',
                'img'=>'https://ss0.bdstatic.com/-0U0bnSm1A5BphGlnYG/tam-ogel/bcd0d5e313a81e499bc3693a6171c577_121_121.jpg',
                'name'=>'国美',
                'admin'=>$website.'/guomei/Admin',
            ),
            array(
                'moudle'=>$website.'/jmyp',
                'img'=>'https://ss2.baidu.com/6ONYsjip0QIZ8tyhnq/it/u=1522247726,2605556307&fm=58&bpow=1024&bpoh=1024',
                'name'=>'聚美优品',
                'admin'=>$website.'/jmyp/Admin',
            ),
            array(
                'moudle'=>$website.'/Kugou',
                'img'=>'https://ss0.baidu.com/6ONWsjip0QIZ8tyhnq/it/u=3674496840,2569142452&fm=58&bpow=500&bpoh=461',
                'name'=>'酷狗',
                'admin'=>$website.'/Kugou/Admin',
            ),
            array(
                'moudle'=>$website.'/58tongcheng',
                'img'=>'https://ss2.bdstatic.com/8_V1bjqh_Q23odCf/pacific/1766813315.jpg',
                'name'=>'58同城',
                'admin'=>$website.'/58tongcheng/Admin',
            ),
            array(
                'moudle'=>$website.'/bww',
                'img'=>'https://ss0.baidu.com/6ONWsjip0QIZ8tyhnq/it/u=571217343,3119542154&fm=58&s=6D47864655E4BB36643E0CBD0300101A&bpow=121&bpoh=75',
                'name'=>'贝瓦网',
                'admin'=>$website.'/bww/Admin',
            ),
            array(
                'moudle'=>$website.'/dbsystem',
                'img'=>'https://ss0.bdstatic.com/70cFuHSh_Q1YnxGkpoWK1HF6hhy/it/u=1588667600,137714162&fm=27&gp=0.jpg',
                'name'=>'蝶变2014',
                'admin'=>$website.'/dbsystem/ShopAdmin',
            ),
            array(
                'moudle'=>$website.'/flower',
                'img'=>'https://ss0.baidu.com/6ONWsjip0QIZ8tyhnq/it/u=3799665570,2642460276&fm=58&s=EC87E71EC5737A300E78C0EC02000033&bpow=121&bpoh=75',
                'name'=>'鲜礼网',
                'admin'=>$website.'/flower/Admin',
            ),
            array(
                'moudle'=>$website.'/ITrecruit',
                'img'=>'https://ss2.baidu.com/6ONYsjip0QIZ8tyhnq/it/u=1527936092,679267761&fm=58&s=8C8D1472649AB62128AE41520300D0BA&bpow=121&bpoh=75',
                'name'=>'ITrecruit',
                'admin'=>$website.'/ITrecruit/Admin',
            ),
            array(
                'moudle'=>$website.'/meizu',
                'img'=>'https://ss1.baidu.com/6ONXsjip0QIZ8tyhnq/it/u=2186657824,641056768&fm=58&bpow=600&bpoh=600',
                'name'=>'魅族',
                'admin'=>$website.'/meizu/Admin',
            ),




        );
        $this->assign("data", $data);


        $this->display();
    }


    public function test(){
        $arr=lock_url('18801043607','Demo','1');
        print_r($arr);
        $data=unlock_url($arr,'Demo');
        dump($_COOKIE);
    }

  

}