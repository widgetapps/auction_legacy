<?php

class Mobile_ScanController extends Auction_Controller_Action
{
    protected $moduleName = 'mobile';
    
    public function init()
    {
    	parent::init();
    	$this->_helper->layout->setLayout('mobile');
    }
    
    public function indexAction()
    {
    }
    
    public function viewAction()
    {
    	list($controltmp, $bin) = explode('|', $this->_getParam('code'));
    	list($control, $auctionId) = explode('_', $controltmp);
    	$controlSource = substr($control, 0, 1);
    	$controlNumber = substr($control, 1);
    	
        $table = new models_vItemDetail();
        $select = $table->select()->where('controlSource = ?', $controlSource)->where('controlNumber = ?', $controlNumber)->where('auctionId = ?', $auctionId);
        try {
        	$row = $table->fetchRow($select);
        } catch (Zend_Db_Exception $e) {
        	echo $controlSource . ';' . $controlNumber . ';' . $auctionId;
        	echo $e->__toString();
        }
 
    	$this->view->item = $row;
    }
}
