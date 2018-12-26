<?php
namespace Jira\Controller;
class EmptyController extends BaseController
{
    public function index()
    {
        $this->redirect('public/404');
    }
}