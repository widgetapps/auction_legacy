<?php

class Facebook_ProfileController extends Auction_Controller_Action
{
    protected $moduleName = 'facebook';
    
    public function init()
    {
    	parent::init();
    	$this->_helper->layout->setlayout('facebook_profile');
    }
    
    public function indexAction()
    {
        require_once('models/vItemList.php');
        $table = new models_vItemList();
        $fselect = $table->select(true)->setIntegrityCheck(false);
        $bselect = $table->select(true)->setIntegrityCheck(false);
        
        $fselect->where('vItemList.auctionId = ?', $this->getCurrentAuctionId())
        		->where('approved = ?', 'y')
        		->where('publish = ?', 'y')
        		->where('featureItem = ?', 'y')
        		->order('fairRetailPrice DESC')
        		->limit(10, 0);
        
        /*
        $bselect->where('vItemList.auctionId = ?', $this->getCurrentAuctionId())
        		->where('approved = ?', 'y')
        		->where('publish = ?', 'y');
        */
        		
        $this->view->fitems = $table->fetchAll($fselect);
        //$this->view->bitems = $table->fetchAll($bselect);
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
