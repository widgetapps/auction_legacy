<?php

class Public_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'public';

    public function init()
    {
        parent::init();

        $this->_helper->layout->setlayout('publicresponsive');
    }


    public function indexAction()
    {

    }

    public function itemsAction()
    {

    }
}
