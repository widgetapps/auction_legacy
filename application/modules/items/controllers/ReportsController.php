<?php

class Items_ReportsController extends Auction_Controller_Action
{
    protected $moduleName = 'items';

    public function indexAction()
    {
        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }
    }
}
