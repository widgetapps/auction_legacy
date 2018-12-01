<?php

class Feeds_BidsController extends Auction_Controller_Action
{
    protected $moduleName = 'feeds';
    
    public function rssAction()
    {
    	header('Content-type: application/rss+xml');
	    $this->_helper->layout->setLayout('feed');
	    
	    $auctionId = $this->getCurrentAuctionId();
	    if ($this->_getParam('id') > 0) {
	    	$auctionId = $this->_getParam('id');
	    }
        
		$blockInfo = $this->getBlockInfo($auctionId);
		$blockEndInfo = $this->getNextBlockInfo($auctionId);
        
        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', explode(':', $blockEndInfo->startTime, -1));
    }
    
    public function xmlAction()
    {
    	header('Content-type: text/xml');
	    $this->_helper->layout->setLayout('feed');
	    
	    $auctionId = $this->getCurrentAuctionId();
	    if ($this->_getParam('id') > 0) {
	    	$auctionId = $this->_getParam('id');
	    }
        
		$blockInfo = $this->getBlockInfo($auctionId);
		$blockEndInfo = $this->getNextBlockInfo($auctionId);
        
        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = explode(':', $blockInfo->startTime);
        $this->view->endTime     = explode(':', $blockEndInfo->startTime);
    }
    
    public function screenAction()
    {
	    $this->_helper->layout->setLayout('bid_screen');
	    
	    $auctionId = $this->getCurrentAuctionId();
	    if ($this->_getParam('id') > 0) {
	    	$auctionId = $this->_getParam('id');
	    }
        
		$blockInfo = $this->getBlockInfo($auctionId);
		$blockEndInfo = $this->getNextBlockInfo($auctionId);
        
        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', explode(':', $blockEndInfo->startTime, -1));
    	
    }
    
    public function boardajaxAction()
    {
        $this->_helper->layout->setLayout('json');
        
        $auctionId = $this->getCurrentAuctionId();
        if ($this->_getParam('id') > 0) {
            $auctionId = $this->_getParam('id');
        }
        
        $blockInfo = $this->getBlockInfo($auctionId);
        $blockEndInfo = $this->getNextBlockInfo($auctionId);
        
        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', explode(':', $blockEndInfo->startTime, -1));
    }

    public function screennewAction()
    {
        $this->_helper->layout->setLayout('bid_screen_new');

        $auctionId = $this->getCurrentAuctionId();
        if ($this->_getParam('id') > 0) {
            $auctionId = $this->_getParam('id');
        }

        $blockInfo = $this->getBlockInfo($auctionId);
        $blockEndInfo = $this->getNextBlockInfo($auctionId);

        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', explode(':', $blockEndInfo->startTime, -1));

    }

    public function boardajaxnewAction()
    {
        $this->_helper->layout->setLayout('json');

        $auctionId = $this->getCurrentAuctionId();
        if ($this->_getParam('id') > 0) {
            $auctionId = $this->_getParam('id');
        }

        $blockInfo = $this->getBlockInfo($auctionId);
        $blockEndInfo = $this->getNextBlockInfo($auctionId);

        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', explode(':', $blockEndInfo->startTime, -1));
    }
    
    public function webboardAction() {
	    $this->_helper->layout->setLayout('webboard');
	    
	    $auctionId = $this->getCurrentAuctionId();
	    if ($this->_getParam('id') > 0) {
	    	$auctionId = $this->_getParam('id');
	    }
        
		$blockInfo = $this->getBlockInfo($auctionId);
		$blockEndInfo = $this->getNextBlockInfo($auctionId);

        $close = explode(':', $blockEndInfo->startTime, -1);
        $close[1] -= 2;
        $close[1] = str_pad($close[1], 2, '0', STR_PAD_LEFT);
        
        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', $close);
    }
    
    public function webboardajaxAction()
    {
        $this->_helper->layout->setLayout('json');
        
        $auctionId = $this->getCurrentAuctionId();
        if ($this->_getParam('id') > 0) {
            $auctionId = $this->_getParam('id');
        }
        
        $blockInfo = $this->getBlockInfo($auctionId);
        $blockEndInfo = $this->getNextBlockInfo($auctionId);

        $close = explode(':', $blockEndInfo->startTime, -1);
        $close[1] -= 2;
        $close[1] = str_pad(close[1], 2, '0', STR_PAD_LEFT);
        
        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', $close);
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
    
    private function getItemsForBid($auctionId)
    {
		$table = new models_vOpenBlockItems();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $auctionId);
        $where[] = $table->getAdapter()->quoteInto('blockId = ?', $this->getCurrentBlockId($auctionId));
        return $table->fetchAll($where, 'controlNumber');
    }
    
    private function getBlockInfo($auctionId)
    {
		$table_block = new models_Block();
		return $table_block->find($this->getCurrentBlockId($auctionId))->current();
    }
    
    private function getNextBlockInfo($auctionId)
    {
		$table_block = new models_Block();
		return $table_block->find($this->getCurrentBlockId($auctionId)+1)->current();
    }
	
	private function getCurrentBlockId($auctionId = null) {
		require_once('models/Auction.php');
		$auction = new models_Auction();
		if ($auctionId != null) {
			$where = "auctionId = " . $auctionId;
		} else {
			$where = "auctionId = " . $this->getCurrentAuctionId();
		}
		$row = $auction->fetchRow($where);
		return $row->currentBlockId;
		
	}
}
