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

    public function bidsAction()
    {
        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }

        $this->_helper->layout->setLayout('reports');
    }

    public function valueAction()
    {
        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }

        $this->_helper->layout->setLayout('reports');
    }

    public function pvalueAction()
    {
        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }

        $this->_helper->layout->setLayout('reports');
    }
}
