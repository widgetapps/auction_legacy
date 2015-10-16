<?php

class Blocks_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'blocks';
    
    public function indexAction()
    {   
        try {
        	$this->authenticateAction('view');
        	$this->_redirector->gotoUrl('/blocks/index/blocklist');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function assignAction() 
    {
    	try {
    		$this->authenticateAction('add');
        	
	        require_once('models/Block.php');
	        $tBlock = new models_Block();
	        require_once('models/Item.php');
	        $tItem = new models_Item();
	        
	        $block = $tBlock->find($this->_getParam('id'))->current();
	        
	        $where = $tItem->getAdapter()->quoteInto('blockId = ?', $this->_getParam('id'));
	        $items  = $tItem->fetchAll($where);
	        
	        $itemNumbers = '';
	        foreach ($items as $item) {
	        	$itemNumbers .= $item->controlNumber . ' ';
	        }
    		
    		$this->view->blockId     = $this->_getParam('id');
    		$this->view->blockNumber = $block->number;
    		$this->view->itemNumbers = trim($itemNumbers);
    	} catch (Metis_Auth_Exception $e){
    		$e->failed();
    		return;
    	}
    }
    
    public function assignprocessAction()
    {
    	try {
    		$this->authenticateAction('add');
    		
	        require_once('models/Item.php');
	        $tItem = new models_Item();
    		
    		$blockId = $this->_getParam('blockId');
    		$controlNumbers = explode(' ', $this->_getParam('itemlist'));
    		
    		$data = array(
    					'blockId' => $blockId
    				);
    		$where = array();
    		$where[] = $tItem->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
    		$where[] = $tItem->getAdapter()->quoteInto('controlNumber IN (' . implode(', ', $controlNumbers) . ')', '');
    		
    		$tItem->update($data, $where);
    		
    		$this->_redirect('/blocks/index/blocklist');
    		
    	} catch (Metis_Auth_Exception $e){
    		$e->failed();
    		return;
    	}
    }
    
    public function blocklistAction()
    {
        try {
        	$this->authenticateAction('view');
	        require_once('models/Auction.php');
	        $tAuction = new models_Auction();
	        
	        $rAuction = $tAuction->find($this->getCurrentAuctionId())->current();
	        $this->view->currentBlockId = $rAuction->currentBlockId;
	        
	        require_once('models/Block.php');
	        $tBlock = new models_Block();
	        
	        $where = $tBlock->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        $order = 'number';
	        $blocks  = $tBlock->fetchAll($where, $order);
	        $this->view->blocks = $blocks;
	        
	        if (count($blocks) == 0){
	        	$this->_redirector->gotoUrl('/blocks/index/newblocksstep1');
	        }
        } catch (Metis_Auth_Exception $e){
            $e->failed();
            return;
        }
    }
    
    public function newblocksstep1Action()
    {
    	try {
    		$this->authenticateAction('add');
    	} catch (Metis_Auth_Exception $e){
    		$e->failed();
    		return;
    	}
    }
    
    public function newblocksstep2Action()
    {
    	try {
    		$this->authenticateAction('add');
    		$this->view->sections = $this->_getParam('blockSections');
    	} catch (Metis_Auth_Exception $e){
    		$e->failed();
    		return;
    	}
    }
    
    public function newblocksprocessAction()
    {        
        try {
        	$this->authenticateAction('add');
        	
	        require_once('models/Auction.php');
	        require_once('models/Block.php');
	        
	        $table_auction = new models_Auction();
	        $row           = $table_auction->find($this->getCurrentAuctionId())->current();
	        
	        $table_block       = new models_Block();
	        $data              = array();
	        $data['auctionId'] = $this->getCurrentAuctionId();
	        $data['closed']    = 'n';
	        
	        $blockNumber = 1;
	        for ($s = 0; $s < $this->_getParam('blockSections'); $s++){
	        	$data['blockDate'] = $this->_getParam('blockDate' . $s);
		        $timeString        = $row->date . ' ' . $this->_getParam('startHour' . $s) . ':' . $this->_getParam('startMinute' . $s) . ':00';
		        $startTime         = strtotime($timeString);
	        		
		        for($i = 0; $i < $this->_getParam('blockNumber' . $s); $i++){
		        	$data['number']    = $blockNumber;
		        	$data['startTime'] = date('H:i:s', $startTime);
		        	$table_block->insert($data);
		        	
		        	$startTime += $this->_getParam('blockLength') * 60;
		        	$blockNumber++;
		        }
	        }
	        $data['number']    = 0;
	        $data['startTime'] = '00:00:00';
	
	        $table_block->insert($data);
	    	
	        $this->_redirect('/blocks/index/blocklist');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
            return;
        }
    }
    
    public function activateAction()
    {
        try {
        	$this->authenticateAction('edit');
        	
	        require_once('models/Auction.php');
	        $tAuction = new models_Auction();
	        
	        $data = array('currentBlockId'   => $this->_getParam('id'));
	        $where = $tAuction->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        
	        $tAuction->update($data, $where);
	        
	        $this->_redirector->gotoUrl('/blocks/index/blocklist#' . ($this->_getParam('id') - 5));
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
            return;
        }
    }
    
    public function openAction()
    {
        try {
        	$this->authenticateAction('edit');
        	
	        require_once('models/Block.php');
	        $tBlock = new models_Block();
	        
	        $data = array('closed' => 'n');
	        
	        if ($this->_getParam('id') == '0'){
	            $where = $tBlock->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        } else {
	            $where = $tBlock->getAdapter()->quoteInto('blockId = ?', $this->_getParam('id'));
	        }
	        
	        $tBlock->update($data, $where);
	        
	        $this->_redirector->gotoUrl('/blocks/index/blocklist#' . ($this->_getParam('id') - 4));
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function closeAction()
    {
        try {
        	$this->authenticateAction('edit');
        
	        require_once('models/Block.php');
	        $tBlock = new models_Block();
	        
	        $data = array('closed' => 'y');
	        
	        if ($this->_getParam('id') == '0'){
	            $where = $tBlock->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        } else {
	            $where = $tBlock->getAdapter()->quoteInto('blockId = ?', $this->_getParam('id'));
	        }
	        
	        $tBlock->update($data, $where);
	        
	        $this->_redirector->gotoUrl('/blocks/index/blocklist#' . ($this->_getParam('id') - 4));
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function bidsAction()
    {
        
        try {
        	$this->authenticateAction('view');
        	
	        require_once('models/Bid.php');
	        require_once('models/Item.php');
	        $tItem = new models_Item();
	        $where = $tItem->getAdapter()->quoteInto('blockId = ?', $this->_getParam('id'));
	        $order = 'controlNumber';
	        
	        $items  = $tItem->fetchAll($where, $order);
	        $this->view->items  = $items;
	        
	        $this->view->bids = array();
	        
	        foreach ($items as $item){
	            $tBid = new models_Bid();
	            $where = $tBid->getAdapter()->quoteInto('itemId = ?', $item->itemId);
	            $order = 'bidTime';
	            $this->view->bids[$item->itemId] = $tBid->fetchAll($where, $order);
	        }
	        
	        $this->view->blockId = $this->_getParam('id');
        } catch (Metis_Auth_Exception $e){
        	$e->failed();
        	return;
        }
    }
    
    public function deletebidAction()
    {
        
        try {
        	$this->authenticateAction('delete');
        	
	        require_once('models/Bid.php');
	        $tBid = new models_Bid();
	        
	        $where = $tBid->getAdapter()->quoteInto('bidId=?', $this->_getParam('id'));
	        
	        $tBid->delete($where);
	        
	        $this->_redirector->gotoUrl('/blocks/index/bids/id/' . $this->_getParam('blockId'));
        } catch (Metis_Auth_Excpetion $e) {
        	$e->failed;
        	return;
        } catch (Zend_Db_Exception $e){
        	return;
        }
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
