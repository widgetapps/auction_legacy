<?php

class Bids_IndexController extends Auction_Controller_Action {
    protected $moduleName = 'bids';
    
    /**
     * Displays the bidding screen where operators can enter bids for the currently available items.
     * All subsequent AJAX requests on this screen are handled by the Ajax Controller in this module.
     *
     */
    function indexAction() {
    	$server = implode('.', explode('.', $_SERVER['SERVER_ADDR'], -1));
    	$remote = implode('.', explode('.', $_SERVER['REMOTE_ADDR'], -1));
    	
        try {
        	$this->authenticateAction('add');
        	if ($this->auth->getIdentity()->role == 'bidder') {
    			$this->_helper->layout->setlayout('bid');
        	}
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
}
?>