<?php

class Mobile_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'mobile';
    
    public function init()
    {
    	parent::init();
    	$this->_helper->layout->setLayout('responsive');
    }
    
    public function indexAction()
    {
    	// Display the current block items, used mainly at the anchor desk. Must poll server for block change
	        
    }
    
    public function bibleAction()
    {
    	// Searchable item list
    	// TODO: Change this to just display full list. OnClick, open a modal (jQuery.load into the modal)
    	require_once('models/vItemDetail.php');
    	$tItems = new models_vItemDetail();
    	
    	$select = $tItems->select();
    	$select->from($tItems);
    	
    	$session_bible = new Zend_Session_Namespace('bible');
    	$type  = $session_bible->type;
    	$sort  = $session_bible->sort;
    	$value = $session_bible->value;
    	
    	if ($this->_getParam('type') != '') {
    		$type  = $this->_getParam('type');
    		$value = $this->_getParam('s');
    	}
    	
    	// There must be a value to search, if not, default to block 1
    	if ($value == '') {
    		$type = 'blockNumber';
    		$value = '1';
    	}
    	
    	switch ($type) {
    		case 'blockNumber':
    			$session_bible->type = 'blockNumber';
    			$session_bible->value = $value;
    			$select->where('blockNumber = ?', $value);
    			break;
    		case 'controlNumber':
    			$session_bible->type = 'controlNumber';
    			$session_bible->value = $value;
    			$select->where('controlNumber = ?', $value);
    			break;
    		case 'itemName':
    			$session_bible->type = 'itemName';
    			$session_bible->value = $value;
    			$select->where('itemName LIKE ?', '%' . $value . '%');
    			break;
    	}
    	
    	switch ($this->_getParam('sort')) {
    		case 'controlNumber':
    			$session_bible->sort = 'controlNumber';
    			$select->order('controlNumber');
    			break;
    		case 'blockNumber':
    		default:
    			$session_bible->sort = 'blockNumber';
    			$select->order('blockNumber');
    			break;
    	}
    	
    	$select->where('auctionId = ?', $this->getCurrentAuctionId());
    	
    	$this->view->items = $tItems->fetchAll($select);
    	$this->view->type = $type;
    	$this->view->value= $value;
    	$this->view->sort = $sort;
    }
    
    public function itemdetailAction()
    {
    	$this->_helper->layout->setLayout('json');
        try {
        	$this->authenticateAction('view');
    	
	    	require_once('models/vItemDetail.php');
	    	$tItems = new models_vItemDetail();
	    	
	    	$this->view->item = $tItems->find($this->_getParam('id'))->current();
        } catch (Metis_Auth_Exception $e) {
        	$this->view->auth = false;
        	return;
        }
    }
    
    public function activeblockAction()
    {
    	$this->_helper->layout->setLayout('json');
    	
        require_once 'models/vItemList.php';
        $table = new models_vItemList();
        
        require_once 'models/Auction.php';
        $tAuction = new models_Auction();
        
        require_once('models/Block.php');
        $tBlock = new models_Block();
        
        $row = $tAuction->find($this->getCurrentAuctionId())->current();
        $activeBlockId = $row->currentBlockId;
	        
	    
        
        if ($activeBlockId == null) {
        	$activeBlockId = 0;
        }
        
        if ($activeBlockId > 0) {
        	$block = $tBlock->find($activeBlockId)->current();
        	
	        $select = $table->select();
			$select->from($table)
			       ->where('blockNumber = ?', $block->number)
			       ->where('auctionId = ?', $this->getCurrentAuctionId());
			$this->view->items = $table->fetchAll($select);
			$this->view->blockNumber = $block->number;
        } else {
        	$this->view->items = 0;
        }
    	
    }
    
}
