<?php

class Pickups_ExportsController extends Auction_Controller_Action
{
    protected $moduleName = 'pickups';
    
    public function indexAction()
    {
    	
    }
    
    public function winningbidderscvsAction()
    {
        try {
        	$this->authenticateAction('view');
	        	
	        $savePath = $this->getExportPath() . DIRECTORY_SEPARATOR . 'winningBidders.csv';
	        $fp = fopen($savePath, 'w');
	        
	        fputcsv($fp, array('Winner Last Name', 'Winner First Name', 'Winner Phone Number'));
	        
	        require_once('models/vPickupList.php');
	        $table = new models_vPickupList();
	        
	        $select = $table->select();
	        
	        $select->where('auctionId = ?', $this->getCurrentAuctionId())
	        	   ->order('winnerLastName', 'winnerFirstName')
	        	   ->group('winnerPhone');
	        	   
	        $items  = $table->fetchAll($select);
	        
			foreach ($items as $item) {
		        
		        $row = array(
		                    $item->winnerLastName,
		                    $item->winnerFirstName,
		                    $item->winnerPhone
		               );
		        
			    fputcsv($fp, $row);
			}
			
			fclose($fp);
			
			$this->_redirector->gotoUrl('/exports/winningBidders.csv');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
    
    private function getPdfPath()
    {
    	return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'pdf';
    }
    
    private function getTemplatePath()
    {
    	return $this->getPdfPath() . DIRECTORY_SEPARATOR . 'templates';
    }
    
    private function getExportPath()
    {
    	return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'exports';
    }
}
