<?php

class Bids_AjaxController extends Auction_Controller_Action {
	protected $moduleName = 'bids';
	
	public function validateitemAction()
	{
        try {
        	$this->authenticateAction('add');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
        
	    $this->_helper->layout->setLayout('json');
		require_once('models/vOpenBlockItems.php');
		$table = new models_vOpenBlockItems();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
        $where[] = $table->getAdapter()->quoteInto('controlNumber = ?', $this->_getParam('id'));
        
		$item = $table->fetchAll($where)->current();
		
		if ($item != null) {
			$bid = ($item->bid == null?0:$item->bid); // Get the current bid
			if ($bid == 0) { // If the there are no bids yet, calculate the min bid
				if ($item->minimumBid == 'y') {
					$minBid = round($item->fairRetailPrice * 0.25); // minimum bid is 1/4 the value
					$minBid -= ($minBid %5); // round down to the nearest increment of 5
					$bid = $minBid - 5;
				} else {
					$bid = 0;
				}
			}
			$outputObject = array('code' => 'success', 'currentBid' => $bid, 'itemName' => $item->name);
		} else {
			$outputObject = array('code' => 'error', 'currentBid' => 'null');
		}
            
		$this->view->jsonCode = Zend_Json::encode($outputObject);
	}
	
	public function getitemlistAction()
	{
        try {
        	$this->authenticateAction('add');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
        
	    $this->_helper->layout->setLayout('fragment');
		require_once('models/vOpenBlockItems.php');
		$table = new models_vOpenBlockItems();
        $where_y = $table->getAdapter()->quoteInto('auctionId = ? AND featureItem="y" AND number > 0', $this->getCurrentAuctionId());
        
        $where_n = array();
        $where_n[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
        $where_n[] = $table->getAdapter()->quoteInto('blockId = ?', $this->getCurrentBlockId());
        // $where_n[] = $table->getAdapter()->quoteInto('featureItem = ?', 'n');
        
        $this->view->items  = $table->fetchAll($where_n, 'controlNumber');
        $this->view->items_feature  = $table->fetchAll($where_y, 'controlNumber');
		
		require_once('models/Block.php');
		$table_block = new models_Block();
		$blockInfo = $table_block->find($this->getCurrentBlockId())->current();
		
		date_default_timezone_set('America/Toronto');
		
		list($shour, $sminute, $ssecond) = explode(':', $blockInfo->startTime);
		list($syear, $smonth, $sday) = explode('-', $blockInfo->blockDate);
		$ts_closetime = mktime($shour, $sminute, $ssecond, $smonth, $sday, $syear) + 600;
			
        $this->view->blockNumber = $blockInfo->number;
        $this->view->currentTime = date('G:i:s');;
        $this->view->countdown   = $this->countdown($ts_closetime);
	}
	
	public function submitbidAction()
	{
        try {
        	$this->authenticateAction('add');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
        
        // List of numbers we don't trust
        $bogusNumbers = array('6476405488', '4167578104', '6475206578', '6474662548', '6474605488');
        
	    $this->_helper->layout->setLayout('json');
		require_once('models/vOpenBlockItems.php');
		require_once('models/Bid.php');
		$blockItems = new models_vOpenBlockItems();
		$where = array();
		$where[] = $blockItems->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
		$where[] = $blockItems->getAdapter()->quoteInto('controlNumber = ?',$this->getRequest()->getParam('itemNo'));
		$row = $blockItems->fetchRow($where);
		
		if ($row->minimumBid == 'y') {
			$minBid = round($row->fairRetailPrice * 0.25);
			$minBid -= ($minBid % 5);
		} else {
			$minBid = 5;
		}
		
		$bidderPhone = $this->getRequest()->getParam('phone1') . $this->getRequest()->getParam('phone2') . $this->getRequest()->getParam('phone3');
		
		if (!$row) {
			$outputObject = array('code' => 'invalidItem');
		} else if (in_array($bidderPhone, $bogusNumbers)) {
			// The phone number is a scammer
			$outputObject = array('code' => 'scammer', 'currentBid' => $minBid);
		} else if (intval($this->getRequest()->getParam('bidAmount')) < $minBid) {
			// The bid is below the minimum bid
			$outputObject = array('code' => 'tooLow', 'currentBid' => $minBid);
		} else if ($row->bid >= intval($this->getRequest()->getParam('bidAmount'))) {
			// The bid is too low or equal to what already exists. 
			$outputObject = array('code' => 'bidCollision', 'currentBid' => $row->bid);
		} else if ($row->bid > 0 && ($row->bid * 5) < intval($this->getRequest()->getParam('bidAmount'))) {
			// The bid is too high. 
			$outputObject = array('code' => 'bidTooHigh', 'currentBid' => $row->bid);
		} else if (strlen($bidderPhone) < 5) {
			// The phone number is too short. 
			$outputObject = array('code' => 'phoneError', 'currentBid' => $row->bid);
		} else {
			// Put in the bid.
			$bid = new models_Bid();
			$newRow = $bid->createRow();
			$newRow->itemId = $row->itemId;
			$newRow->bidderPhone = 	$bidderPhone;
			$newRow->bid = $this->getRequest()->getParam('bidAmount');
			$newRow->bidTime = date('Y-m-d H:i:s');
			$newRow->personId = 0; // no person id right now.
			$newRow->called = 'n';
			$newRow->paid = 'n';
			$newRow->save();
			$outputObject = array('code' => 'success', 'currentBid' => $newRow->bid);
		}
		
		$this->view->jsonCode = Zend_Json::encode($outputObject);
	}
	
	private function getCurrentBlockId() {
		require_once('models/Auction.php');
		$auction = new models_Auction();
		$where = "auctionId = " . $this->getCurrentAuctionId();
		$row = $auction->fetchRow($where);
		return $row->currentBlockId;
		
	}
	
	private function countdown($then) {
		$now = time();
		$till = $then-$now; // seconds until $then
		
		$minutes = floor($till / 60);
		$seconds = $till % 60;
		
		if ($seconds < 10){
			$seconds = '0' . $seconds;
		}
		
		$cd_string = $minutes . ':' . $seconds;
		
		return $cd_string;
	}
	
}

?>