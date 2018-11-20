<?php
namespace Admin\Controller;
use Think\Controller;
class EmptyController extends Controller
{
    public function index()
    {
        $this->redirect('public/404');
    }
}