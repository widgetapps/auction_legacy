<?php

class Pickups_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'pickups';
    
    /*
     * NEW PROCESS
     * User enters customer phone number
     * System displays all items the customer won & their contact info
     * User clicks on "print work order" which is used by runners to find the item
     * System drives to the next list.
     * User can select the items the customer wants
     * User click "Print Invoice"
     * System prints invoice
     * System clears the winning bid
     * User clicks "Done"
     */
    
    // TODO: Looking up a customer
    public function indexAction()
    {
        try {
        	$this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
        //$this->_redirector->gotoUrl('/pickups/index/search');
    }
    
    public function lookupAction()
    {
        try {
        	$this->authenticateAction('view');
	        require_once 'models/vItemWinner.php';
	        $table = new models_vItemWinner();
	        
	        $select = $table->select();
			$select->from($table)
			       ->where('auctionId = ?', $this->getCurrentAuctionId())
			       ->where('phone = ?', $this->_getParam('phone'))
			       ->where('paid = ?' , 'n');
			$rows = $table->fetchAll($select);
			
			$row = $rows->current();
			$this->view->customer = $row;
			$this->view->items    = $rows;
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    // TODO: Add row to the invoice tables and display the invoice to be printed.
    public function createinvoiceAction()
    {
    	/*
    	 * Create the invoice record
    	 * Loop through the winning items
    	 * For each item selected, create an invoice item record
    	 * For each item not selected, update the bid so there is no personId (aka: no winner)
    	 * Display the result
    	 */
        try {
        	$this->authenticateAction('view');
        	
        	// get the array of items the customer wants
        	$want = $this->_getParam('want');
	        
        	// Get the items the customer has won
	        $table = new models_vItemWinner();
	        $select = $table->select();
			$select->from($table)
			       ->where('auctionId = ?', $this->getCurrentAuctionId())
			       ->where('phone = ?', $this->_getParam('phone'))
			       ->where('paid = ?', 'n');
			$rows_winner = $table->fetchAll($select);
        	
			// See if they want stuff
        	if (is_array($want) && count($want) > 0) {
        		// They want stuff, create an invoice for the stuff they want
				$winner = $rows_winner->current();
				
				// Insert the Invoice record
				$table = new models_Invoice();
				$data = array(
							'auctionId' => $this->getCurrentAuctionId(),
							'personId'  => $winner->personId,
							'number'    => $this->getNextInvoiceNumber(),
							'date'      => date('Y-m-d H:i:s')
						);
				$invoiceId = $table->insert($data);
				
                $bellRinger = false;
                $bellRingerCount = 0;
				
				// add each invoice item for every item they want
				foreach ($rows_winner as $item){
					if (in_array($item->bidId, $want)) {
						// cusomter wants it, insert it
						$table_ii = new models_InvoiceItem();
						$data = array(
									'invoiceId'  => $invoiceId,
									'itemId'     => $item->itemId,
									'itemNumber' => $item->controlNumber,
									'itemName'   => $item->itemName,
									'winningBid' => $item->bid
								);
						$table_ii->insert($data);
    
                        if ($item->bid >=100 && $item->bid >= $item->itemValue) {
                            $bellRinger = true;
                            $bellRingerCount++;
                        }
					} else {
						// customer doesn't want it, free up the item for call backs
		        		require_once 'models/Bid.php';
		        		$table_bid = new models_Bid();
	        			$bid = $table_bid->find($item->bidId)->current();
	        			$bid->personId = 0;
	        			$bid->paid     = 'n';
	        			$bid->save();
					}
				}
                
                // Add in shipping charge if being delivered
//                 if ($this->_getParam('deliver') == 'y') {
//                     $table_ii = new models_InvoiceItem();
//                     $data = array(
//                                 'invoiceId'  => $invoiceId,
//                                 'itemId'     => 0,
//                                 'itemNumber' => 0,
//                                 'itemName'   => 'Shipping charge',
//                                 'winningBid' => 15
//                             );
//                     $table_ii->insert($data);
//                 }
                
                 // Add in  bell ringer
                 if ($bellRinger) {
                     $table_ii = new models_InvoiceItem();
                     $data = array(
                                 'invoiceId'  => $invoiceId,
                                 'itemId'     => 0,
                                 'itemNumber' => 0,
                                 'itemName'   => 'Free Bell Ringers: ' . $bellRingerCount,
                                 'winningBid' => 0
                             );
                     $table_ii->insert($data);
                 }
				
				$this->_redirector->gotoUrl('/pickups/index/invoice/id/' . $invoiceId);
        	} else {
        		// They don't wan't nothin', so clear the personId's for the winning items.
        		require_once 'models/Bid.php';
        		$table_bid = new models_Bid();
        		
        		foreach ($rows_winner as $winner) {
        			$bid = $table_bid->find($winner->bidId)->current();
        			$bid->personId = 0;
        			$bid->paid     = 'n';
        			$bid->save();
        		}
        		
        		$this->_redirector->gotoUrl('/pickups');
        	}
			
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    public function invoiceAction()
    {
    	// Show the invoice, have a PAID button that goes to the invoicepaid action and a CANCEL button that goes to the cancel action
        try {
        	$this->authenticateAction('view');
			
			$table_invoice = new models_Invoice();
			$invoice = $table_invoice->find($this->_getParam('id'))->current();
			
			$table_person = new models_Person();
			$person = $table_person->find($invoice->personId)->current();
			
			$table_card = new models_Card();
			$select_card = $table_card->select();
			$select_card->where('personId = ?', $invoice->personId);
			$card = $table_card->fetchRow($select_card);
			
			$table_iitems = new models_InvoiceItem();
	        $select = $table_iitems->select();
			$select->where('invoiceId = ?', $invoice->invoiceId);
			$iitems = $table_iitems->fetchAll($select);
			
	    	if ($card) {
	    		require 'Auction/Crypt.php';
	    		$crypt = new Auction_Crypt();
	    		
		    	$key = $crypt->pbkdf2($this->resources['appconfig']['pass'], $this->resources['appconfig']['salt'], 20000, 32);
		    	
		    	$cardinfo = array(
		    					'name'   => $card->name,
		    					'type'   => $card->type,
		    					'number' => $crypt->decrypt($card->number, $key),
		    					'expiry' => $crypt->decrypt($card->expiry, $key),
		    					'cvv'    => $crypt->decrypt($card->cvv, $key)
		    				);
		    	$this->view->cardinfo = $cardinfo;
	    	}
			
			$this->view->person        = $person;
			$this->view->invoice       = $invoice;
			$this->view->invoiceItems  = $iitems;
			$this->view->invoiceNumber = $this->getCurrentAuctionId() . '-' . $invoice->number;
			
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	
        }
    }
    
    public function invoicepaidAction()
    {
    	// Items need to be flagged pickedUp and bids need to be flagged paid. Have a PRINT button and a DONE button
        try {
        	$this->authenticateAction('view');
			
			$table_invoice = new models_Invoice();
			$invoice = $table_invoice->find($this->_getParam('invoiceId'))->current();
			$invoice->paymentType = $this->_getParam('paymentType');
			$invoice->save();
			
			$table_person = new models_Person();
			$person = $table_person->find($invoice->personId)->current();
			
			$table_iitems = new models_InvoiceItem();
	        $select = $table_iitems->select();
			$select->where('invoiceId = ?', $invoice->invoiceId);
			$iitems = $table_iitems->fetchAll($select);
			
			foreach ($iitems as $iitem) {
	        	$table_winner = new models_vItemWinner();
				$winner = $table_winner->find($iitem->itemId)->current();

				// Flagged paid & picked up if not a bell ringer
				if ($winner->bidId > 0) {
					$table_bid = new models_Bid();
					$bid = $table_bid->find($winner->bidId)->current();
					$bid->paid = 'y';
					$bid->save();

					$table_item = new models_Item();
					$item = $table_item->find($winner->itemId)->current();
					$item->pickedUp = 'y';
					$item->save();
				}
			}
			
			$this->view->person       = $person;
			$this->view->invoice      = $invoice;
			$this->view->invoiceItems = $iitems;
			$this->view->invoiceNumber = $this->getCurrentAuctionId() . '-' . $invoice->number;
			
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	echo '<!--' . $e->getMessage() . '-->';
        }
    }
    
    public function invoicecancelAction()
    {
    	// clear the personId for the stuff they don't want
        try {
        	$this->authenticateAction('view');
			
			$table_invoice = new models_Invoice();
			$invoice = $table_invoice->find($this->_getParam('invoiceId'))->current();
			
			$table_iitems = new models_InvoiceItem();
	        $select = $table_iitems->select();
			$select->where('invoiceId = ?', $invoice->invoiceId);
			$iitems = $table_iitems->fetchAll($select);
			
			foreach ($iitems as $iitem) {
	        	$table_winner = new models_vItemWinner();
				$winner = $table_winner->find($iitem->itemId)->current();
				
        		$table_bid = new models_Bid();
        		$bid = $table_bid->find($winner->bidId)->current();
        		$bid->personId = 0;
        		$bid->paid     = 'n';
        		//echo '<pre>'; var_dump($bid); echo '</pre>';
        		$bid->save();
        		//echo $bid->bidId . '<br />';
        		$iitem->delete();
			}
			
			$invoice->delete();
			
			$this->_redirector->gotoUrl('/pickups');
			
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	echo '<!--' . $e->getMessage() . '-->';
        }
    }
    
    public function customersAction()
    {
        try {
        	$this->authenticateAction('view');
	    	
	    	$table_invoice = new models_Invoice();
	    	$select = $table_invoice->select(true)->setIntegrityCheck(false);
	    	$select->where('Invoice.auctionId = ?', $this->getCurrentAuctionId())
	    			->join('Person', 'Invoice.personId = Person.personId', array('firstName', 'lastName', 'phone'));
	    	$this->view->invoices  = $table_invoice->fetchAll($select);
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	echo '<!--' . $e->getMessage() . '-->';
        }
    }
    
    public function viewinvoiceAction()
    {
    	try {
    		$this->authenticateAction('view');
			
			$table_invoice = new models_Invoice();
			$invoice = $table_invoice->find($this->_getParam('id'))->current();
			
			$table_person = new models_Person();
			$person = $table_person->find($invoice->personId)->current();
			
			$table_card = new models_Card();
			$select_card = $table_card->select();
			$select_card->where('personId = ?', $invoice->personId);
			$card = $table_card->fetchRow($select_card);
			
			$table_iitems = new models_InvoiceItem();
	        $select = $table_iitems->select();
			$select->where('invoiceId = ?', $invoice->invoiceId);
			$iitems = $table_iitems->fetchAll($select);
			
	    	if ($card) {
	    		require 'Auction/Crypt.php';
	    		$crypt = new Auction_Crypt();
	    		
		    	$key = $crypt->pbkdf2($this->resources['appconfig']['pass'], $this->resources['appconfig']['salt'], 20000, 32);
		    	
		    	$cardinfo = array(
		    					'name'   => $card->name,
		    					'type'   => $card->type,
		    					'number' => $crypt->decrypt($card->number, $key),
		    					'expiry' => $crypt->decrypt($card->expiry, $key),
		    					'cvv'    => $crypt->decrypt($card->cvv, $key)
		    				);
		    	$this->view->cardinfo = $cardinfo;
	    	}
			
			$this->view->person        = $person;
			$this->view->invoice       = $invoice;
			$this->view->invoiceItems  = $iitems;
			$this->view->invoiceNumber = $this->getCurrentAuctionId() . '-' . $invoice->number;
    	} catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	echo '<!--' . $e->getMessage() . '-->';
        }
    }
    
    
    public function itemsAction()
    {
        try {
        	$this->authenticateAction('view');
        	$table = new models_vPickupList();
	        
	        $session_itemList = new Zend_Session_Namespace('items_pickups');
	        $order = $session_itemList->sort;
	        
	        switch ($this->_getParam('sort')){
	            case 'controlNumber':
	                $order = 'controlNumber';
	                $session_itemList->sort = $order;
	                break;
	            case 'winnerPhone':
	                $order = 'winnerPhone';
	                $session_itemList->sort = $order;
	                break;
	            default:
	            	$order = array();
	                $order[] = 'pickedUp';
	                $order[] = 'winnerPhone';
	                $session_itemList->sort = $order;
	        }
	        
        	$select = $table->select();
        	$select->where('auctionId = ?', $this->getCurrentAuctionId())->order($order);
        	$this->view->items = $table->fetchAll($select);
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        } catch (Zend_Db_Exception $e) {
        	echo '<!--' . $e->getMessage() . '-->';
        }
    }
    
    // A list of work orders, by customer phone number
    // Get list of items, grouped by personId (phone #). Only items marked as not paid/picked-up and for delivery
    // Each item in the list has a link to view work order, this goes to the existing 'lookup' action
    // Regular process should work from there.
    public function shippinglistAction()
    {
    	
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
    
    private function getNextInvoiceNumber()
    {
    	// This uses the current auction ID as a prefix and then the next available invoice number.
        $table = new models_Invoice();
        
        $where   = array();
        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
        $order = 'number DESC';
        
        $rowset  = $table->fetchAll($where, $order);
        
        if (count($rowset) > 0){
	        $row = $rowset->current();
	        return (int)$row->number + 1;
        }
        
        return 1;
    }
}
