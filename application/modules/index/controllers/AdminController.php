<?php

class AdminController extends Auction_Controller_Action
{
    protected $moduleName = 'index';
    
    public function indexAction()
    {
        $this->_redirect('/index/admin/changeyear');
    }
    
    public function changeyearAction()
    {
        require_once('models/Auction.php');
        $table = new models_Auction();
        $rows = $table->fetchAll();
        
        $auctionRows = array();
        foreach ($rows as $row){
        	$auctionRows[$row->auctionId] = $row->name;
        }
        
        $this->view->currentAuctionId = $this->getCurrentAuctionId();
    	$this->view->auctionRows = $auctionRows;
    }
    
    public function changeyearprocessAction()
    {
    	$this->sessionNamespace->sessionAuctionId = $this->_getParam('auctionId');
    }
    
    public function setupAcl()
    {
    	
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
