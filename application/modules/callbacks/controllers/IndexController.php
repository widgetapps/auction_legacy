<?php

class Callbacks_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'people';
    
    public function indexAction()
    {   
        try {
        	$this->authenticateAction('view');
        	$this->_redirector->gotoUrl('/callbacks/index/blocklist');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function blocklistAction()
    {
        try {
        	$this->authenticateAction('view');
	        	
	        require_once('models/Block.php');
	        $tBlock = new models_Block();
	        
	        $where = array();
	        $where[] = $tBlock->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        $where[] = $tBlock->getAdapter()->quoteInto('closed = ?', 'y');
	        $order = 'number DESC';
	        
	        $this->view->blocks  = $tBlock->fetchAll($where, $order);
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    public function summaryAction()
    {
        try {
        	$this->authenticateAction('view');
	        
	        require_once 'models/Block.php';
	        $table_block = new models_Block();
	        $select = $table_block->select();
	        $select->where('blockId = ?', $this->_getParam('block'));
	        $block = $table_block->fetchRow($select);
	        
	        require_once 'models/vItemWinner.php';
	        $table = new models_vItemWinner();
	        
	        $select = $table->select();
			$select->from($table)
			       ->where('blockId = ?', $block->blockId);
			$items = $table->fetchAll($select);
	        
	        $this->view->block = $block;
	        $this->view->items = $items;
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    public function viewblockAction()
    {
        try {
        	$this->authenticateAction('view');
        	
	        require_once('models/Item.php');
	        $tItem = new models_Item();
	        $where = $tItem->getAdapter()->quoteInto('blockId = ?', $this->_getParam('id'));
	        $order = 'itemNumber';
	        
	        $items  = $tItem->fetchAll($where, $order);
	        $this->view->items  = $items;
	        
	        require_once 'models/Block.php';
	        $tBlock = new models_Block();
	        $row = $tBlock->find($this->_getParam('id'))->current();
	        
	        $this->view->blockId = $this->_getParam('id');
	        $this->view->blockNumber = $row->number;
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    public function viewwinnerAction()
    {
        try {
        	$this->authenticateAction('view');
        	
	        require_once 'models/Item.php';
	        require_once 'models/Bid.php';
	        require_once 'models/Block.php';
	        require_once 'models/Person.php';
	        
	        $tBlock = new models_Block();
	        $rBlock = $tBlock->find($this->_getParam('blockId'))->current();
	        
	        $tItem = new models_Item();
	        $rItem = $tItem->find($this->_getParam('id'))->current();
	        
	        $tBid = new models_Bid();
	        $where = $tBid->getAdapter()->quoteInto('itemId = ?', $this->_getParam('id'));
	        $order = 'bid DESC';
	        $limit = 5;
	        $rsBid = $tBid->fetchAll($where, $order, $limit);
	        
			$rPerson = null;
			$winningBid = 0;
	        foreach ($rsBid as $bid){
	            if ($bid->personId != 0){
			        $tPerson = new models_Person();
			        $tPerson = new models_Person();
			        $rPerson = $tPerson->find($bid->personId)->current();
			        $winningBid = $bid->bidId;
	            }
	        }
	        
	        $this->view->winningBidId = $winningBid;
	        $this->view->item = $rItem;
	        $this->view->bids = $rsBid;
	        $this->view->provinceArray = $this->getProvinceArray();
	        $this->view->blockId = $this->_getParam('blockId');
	        $this->view->blockNumber = $rBlock->number;
	        $this->view->person = $rPerson;
        } catch (Metis_Auth_Exdeption $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    public function viewsheetAction()
    {
        try {
        	$this->authenticateAction('view');
        	
	        require_once 'models/Item.php';
	        require_once 'models/Bid.php';
	        require_once 'models/Block.php';
	        require_once 'models/Person.php';
	        
	        $tBlock = new models_Block();
	        $rBlock = $tBlock->find($this->_getParam('blockId'))->current();
	        
	        $tItem = new models_Item();
	        $rItem = $tItem->find($this->_getParam('id'))->current();
	        
	        $tBid = new models_Bid();
	        $where = $tBid->getAdapter()->quoteInto('itemId = ?', $this->_getParam('id'));
	        $order = 'bid DESC';
	        $limit = 5;
	        $rsBid = $tBid->fetchAll($where, $order, $limit);
	        
			$rPerson = null;
			$winningBid = 0;
	        foreach ($rsBid as $bid){
	            if ($bid->personId != 0){
			        $tPerson = new models_Person();
			        $tPerson = new models_Person();
			        $rPerson = $tPerson->find($bid->personId)->current();
			        $winningBid = $bid->bidId;
	            }
	        }
	        
	        $this->view->winningBidId = $winningBid;
	        $this->view->item = $rItem;
	        $this->view->bids = $rsBid;
	        $this->view->provinceArray = $this->getProvinceArray();
	        $this->view->blockId = $this->_getParam('blockId');
	        $this->view->blockNumber = $rBlock->number;
	        $this->view->person = $rPerson;
	        // View the callback for one item
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    // Use the winning bidder and entered phone number to confirm the winner info or add winner info
    public function confirmwinnerAction()
    {
    	/*
    	 * Need to look up the bid & item info so the user can see what item they are workign on
    	 * Need to look up the phone number to see if it's in the system. If it is, load the person data into the form
    	 * If the user doesn't exits, show the credit card fields for entry.
    	 * If the user exists, display last 4 digital of card. Allow user to edit if it's wrong. Keep in mind only one card can be used for a person.
    	 * User confirms/fills out the user info and submits.
    	 */
    	
        try {
        	$this->authenticateAction('view');
        	
        	if ($this->_getParam('winningBid') == 'NA') {
        		// delete the bids and assign the item to block 0 and item 0
        		
        		require_once 'models/Bid.php';
        		$table_bid = new models_Bid();
        		$where = $table_bid->getAdapter()->quoteInto('itemId = ?', $this->_getParam('itemId'));
        		$table_bid->delete($where);
        		
        		require_once 'models/Block.php';
        		$table_block = new models_Block();
        		$select = $table_block->select();
        		$select->where('auctionId = ?', $this->getCurrentAuctionId())
        				->where('number = ?', 0);
        		$block = $table_block->fetchRow($select);
        		
        		require_once 'models/Item.php';
        		$table_item = new models_Item();
        		$item = $table_item->find($this->_getParam('itemId'))->current();
        		$item->itemNumber = 0;
        		$item->blockId    = $block->blockId;
        		$item->save();
        		
        		$this->_redirector->gotoUrl('/callbacks/index/viewblock/id/' . $this->_getParam('blockId'));
        		return;
        	}
        	
	    	$this->view->validPhone = true;
	    	
	    	// TODO: Also check that the number is ten integers.
            /*
	    	if (strlen($this->_getParam('winnerPhone')) != 10) {
	    		$this->view->validPhone = false;
	    		return;
	    	}
            */
	        
	        require_once('models/Person.php');
	        $table = new models_Person();
	        $select = $table->select();
			$select->from($table)
			       ->where('phone = ?', $this->_getParam('winnerPhone'));
			$rows_person = $table->fetchAll($select);
			
			$person    = false;
			$card      = false;
			$cardNum   = false;
            
			if (count($rows_person) > 0) {
				$person = $rows_person->current();
	        
		        require_once('models/Card.php');
		        $table = new models_Card();
		        $select = $table->select();
				$select->from($table)
				       ->where('personId = ?', $person->personId);
				$rows_card = $table->fetchAll($select);
				
				if (count($rows_card) > 0) {
					$card = $rows_card->current();
					
			    	$crypt = new Auction_Crypt();
			    	
			    	$key = $crypt->pbkdf2($this->resources['appconfig']['pass'], $this->resources['appconfig']['salt'], 20000, 32);
			    	$cardNum = '************' . substr($crypt->decrypt($card->number, $key), -4);
			    	// Don't need the expiry & cvv decryted since we're not going to display.
			    	//$expiry  = $crypt->decrypt($card->expiry, $key);
			    	//$ccv     = $crypt->decrypt($card->cvv, $key);
				}
			}
	    	
	        require_once('models/Item.php');
	        $table = new models_Item();
	        $item = $table->find($this->_getParam('itemId'))->current();
	    	
	        require_once('models/Block.php');
	        $table = new models_Block();
	        $block = $table->find($this->_getParam('blockId'))->current();
	    	
	        require_once('models/Bid.php');
	        $table = new models_Bid();
	        $bid = $table->find($this->_getParam('winningBid'))->current();
            
            // Get the other items the customer has won
            $table = new models_vItemWinner();
            $select = $table->select();
            $select->from($table)
                   ->where('auctionId = ?', $this->getCurrentAuctionId())
                   ->where('phone = ?', $this->_getParam('winnerPhone'))
                   ->order('itemNumber');
            $itemsWon = $table->fetchAll($select);
	    	
	        $this->view->creditcard = $cardNum;
	        $this->view->person     = $person;
	        $this->view->block      = $block;
	        $this->view->item       = $item;
	        $this->view->bid        = $bid;
	        $this->view->phone      = $this->_getParam('winnerPhone');
            $this->view->itemsWon   = $itemsWon;
	    	
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    public function confirmwinnerprocessAction()
    {
    	// save data to the database, including the credit card info
        try {
        	$this->authenticateAction('edit');
        	
	        // Insert or update the person information
	        require_once('models/Person.php');
	        $table_person = new models_Person();
	        
	        $personId = null;
	        
	        $data_person = array(
	                    'firstName'    => $this->_getParam('firstName'),
	                    'lastName'     => $this->_getParam('lastName'),
	                    'address1'     => $this->_getParam('address1'),
	                    'address2'     => $this->_getParam('address2'),
	                    'city'         => $this->_getParam('city'),
	                    'province'     => $this->_getParam('province'),
	                    'country'      => 'ca',
	                    'postalCode'   => $this->_getParam('postalCode'),
	                    'phone'        => $this->_getParam('phone'),
	                    'email'        => $this->_getParam('emailAddress')
	                );
	        
	        if ($person = $this->personExists($this->_getParam('phone'))){
		        $personId = $person->personId;
		        $where = $table_person->getAdapter()->quoteInto('personId = ?', $personId);
		        $person->setFromArray($data_person);
		        $person->save();
	        } else {
	            $data_person['canvasser'] = 'n';
	            $data_person['donor']     = 'n';
                try {
                	$personId = $table_person->insert($data_person);
                } catch (Zend_Db_Exception $e) {
                    $this->view->backtrace = $e->getMessage();
                    return;
                }
		        
	        }
	        
	        // Deal with the credit card info
	        if (in_array($this->_getParam('cardType'), array('1', '2'))) {
				
	        	// Encrypt the card info
		    	$crypt = new Auction_Crypt();
		    	
		    	$key = $crypt->pbkdf2($this->resources['appconfig']['pass'], $this->resources['appconfig']['salt'], 20000, 32);
		        
		        $cardInfo = array (
		        				'name'   => $this->_getParam('nameOnCard'),
		        				'number' => $crypt->encrypt($this->_getParam('cardNumber'), $key),
		        				'expiry' => $crypt->encrypt($this->_getParam('cardExpiryMonth') . '/' . $this->_getParam('cardExpiryYear'), $key),
		        				'cvv'    => $crypt->encrypt($this->_getParam('cardCvv'), $key),
		        				'type'   => $this->_getParam('cardType')
		        			);
		        
		        // Insert/update the card info in the DB
		        require_once 'models/Card.php';
		        $table_card = new models_Card();
		        if ($card = $this->cardExists($personId)) {
		        	// do an update
		        	$card->setFromArray($cardInfo);
			        $card->save();
		        } else {
		        	// do an insert
		        	$cardInfo['personId'] = $personId;
		        	$cardId = $table_card->insert($cardInfo);
		        }
	        }
	        
	        require_once 'models/Bid.php';
	        
	        // Remove personId from all previous bids on this item
	        $tBid = new models_Bid();
	        $biddata = array(
	                    'personId' => 0
	                );
	        $where = $tBid->getAdapter()->quoteInto('itemId = ?', $this->_getParam('itemId'));
	        $tBid->update($biddata, $where);
	        
	        // Update the bid with the personId
	        $tBid = new models_Bid();
	        $biddata = array(
	                    'personId' => $personId,
	                    'called' => 'y'
	                );
	        $where = $tBid->getAdapter()->quoteInto('bidId = ?', $this->_getParam('bidId'));
	        $tBid->update($biddata, $where);
	        
	        // update item delivery status
	        $table = new models_Item();
	        $item = $table->find($this->_getParam('itemId'))->current();
	        $item->deliver = ($this->_getParam('delivery')?$this->_getParam('delivery'):'n');
	        $item->save();
        
	        // send the email is required
	        if ($this->_getParam('emailAddress') != '' && $this->_getParam('sendEmail') == 'yes') {
				$mail = new Zend_Mail();
				$mail->setBodyHtml($this->formatWinnerEmail($item));
				$mail->setFrom('admin@metrotorontorotaryauction.com', 'MTRA Admin');
				$mail->addTo($this->_getParam('emailAddress'), $this->_getParam('emailAddress'));
				$mail->setSubject('[Metro TorontoRotary TV Auction] Pick-up Instructions');
				try {
				    $mail->send();
				} catch (Exception $e){}
	        }
	        
	        // Redirect to the block
	        $this->_redirector->gotoUrl('/callbacks/index/viewblock/id/' . $this->_getParam('blockId'));
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        }
    	
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
    
    private function personExists($phone)
    {
        require_once('models/Person.php');
        $table = new models_Person();
        $where = array('phone = ?'   => $phone);
        $rows  = $table->fetchAll($where);
        
        if (count($rows) > 0){
            return $rows->current();
        }
        
        return false;
    }
    
    private function cardExists($personId)
    {
        require_once('models/Card.php');
        $table = new models_Card();
        $where = array('personId = ?' => $personId);
        $rows  = $table->fetchAll($where);
        
        if (count($rows) > 0){
            return $rows->current();
        }
        
        return false;
    }
    
    private function formatWinnerEmail()
    {
        $emailTemplate = new Zend_Layout();
        $scriptPath = APPLICATION_PATH
        . DIRECTORY_SEPARATOR . 'layouts'
        . DIRECTORY_SEPARATOR . 'emails';
        $emailTemplate->setLayoutPath($scriptPath);
        $emailTemplate->setLayout('winneremail');
        
        return $emailTemplate->render();
    }
    
}
