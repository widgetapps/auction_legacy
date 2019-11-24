<?php

class Pickups_ExportsController extends Auction_Controller_Action
{
    protected $moduleName = 'pickups';
    
    public function indexAction()
    {
    	
    }

    public function itemsalescvsAction()
    {
        try {
            $this->authenticateAction('view');

            $savePath = $this->getExportPath() . DIRECTORY_SEPARATOR . 'item_bids_' .  $this->view->getActiveAuctionDate(1) . '.csv';
            $fp = fopen($savePath, 'w');

            fputcsv($fp, array(
                'Control Source',
                'Control Number',
                'Item Name',
                'Item Value',
                'Bid',
                'Percent Value',
                'Donor Company',
                'Donor First Name',
                'Donor Last Name',
                'Winner',
                'Winner Phone',
                'Paid'
            ));

            require_once('models/vItemWinner.php');
            $table = new models_vItemWinner();

            $select = $table->select(true)->setIntegrityCheck(false);

            $select
                ->from(
                    array('A' => 'vItemWinner'),
                    array(
                      'controlSource',
                      'controlNumber',
                      'itemName',
                      'itemValue',
                      'bid',
                      'paid',
                      'winnerPhone' => 'phone'
                    )
                )
                ->join(
                    array('B' => 'Person'),
                    'A.donorId = B.personId',
                    array(
                        'donorCompany' => 'companyName',
                        'donorFirstName' => 'firstName',
                        'donorLastName' => 'lastName'
                    )
                )
                ->where('auctionId = ?', $this->getCurrentAuctionId())
                ->order('A.controlSource', 'A.itemNumber', 'A.paid DESC');

            $items  = $table->fetchAll($select);

            foreach ($items as $item) {

                $row = array(
                    $item->controlSource,
                    $item->controlNumber,
                    $item->itemName,
                    $item->itemValue,
                    $item->bid,
                    $item->bid / $item->itemValue,
                    $item->donorCompany,
                    $item->donorFirstName,
                    $item->donorLastName,
                    $item->firstname . ' ' . $item->lastname,
                    $item->winnerPhone,
                    $item->paid
                );

                fputcsv($fp, $row);
            }

            fclose($fp);

            $this->_redirector->gotoUrl('/exports/item_bids_' .  $this->view->getActiveAuctionDate(1) . '.csv');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
            return;
        }
    }

    public function donortotalsscvsAction()
    {
        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
            return;
        }
    }
    
    public function winningbidderscvsAction()
    {
        try {
        	$this->authenticateAction('view');
	        	
	        $savePath = $this->getExportPath() . DIRECTORY_SEPARATOR . 'winningBidders_' .  $this->view->getActiveAuctionDate(1) . '.csv';
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
			
			$this->_redirector->gotoUrl('/exports/winningBidders_' .  $this->view->getActiveAuctionDate(1) . '.csv');
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
