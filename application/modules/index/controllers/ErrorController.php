<?php

class ErrorController extends Auction_Controller_Action
{
    public function errorAction()
    {
        $this->view->backtrace = debug_backtrace();
        //$this->view->backtrace = 'There was a problem.';
    }
}
