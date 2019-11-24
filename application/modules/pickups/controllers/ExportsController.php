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
                'Canvasser First Name',
                'Canvasser Last Name',
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

            $select->where('auctionId = ?', $this->getCurrentAuctionId());
            $select->join(
                array('p1' => 'Person'),
                'vItemWinner.donorId = p1.personId',
                array(
                    'donorCompany' => 'companyName',
                    'donorFirstName' => 'firstName',
                    'donorLastName' => 'lastName'
                )
            );
            $select->join(
                array('p2' => 'Person'),
                'vItemWinner.canvasserId = p2.personId',
                array(
                    'canvasserFirstName' => 'firstName',
                    'canvasserLastName' => 'lastName'
                )
            );
            $select->order('vItemWinner.controlSource', 'vItemWinner.itemNumber', 'vItemWinner.paid DESC');

            $items  = $table->fetchAll($select);

            foreach ($items as $item) {

                $row = array(
                    $item->controlSource,
                    $item->controlNumber,
                    $item->itemName,
                    $item->itemValue,
                    $item->bid,
                    $item->bid / $item->itemValue,
                    $item->canvasserFirstName,
                    $item->canvasserLastName,
                    $item->donorCompany,
                    $item->donorFirstName,
                    $item->donorLastName,
                    $item->firstName . ' ' . $item->lastName,
                    $item->phone,
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

            $savePath = $this->getExportPath() . DIRECTORY_SEPARATOR . 'donor_totals_' .  $this->view->getActiveAuctionDate(1) . '.csv';
            $fp = fopen($savePath, 'w');

            fputcsv($fp, array(
                'Control Source',
                'Company Name',
                'First Name',
                'Last Name',
                'Address 1',
                'Address 2',
                'City',
                'Province',
                'Postal Code',
                'Phone',
                'Total'
            ));

            require_once('models/Item.php');
            $table = new models_Item();

            $select = $table->select(true)->setIntegrityCheck(false);

            $select->where('Item.auctionId = ?', $this->getCurrentAuctionId());
            $select->where('Item.taxReceipt = ?', 'y');
            $select->join(
                array('Person'),
                'Item.donorId = Person.personId',
                array(
                    'donorCompany' => 'companyName',
                    'donorFirstName' => 'firstName',
                    'donorLastName' => 'lastName',
                    'donorAddress1' => 'address1',
                    'donorAddress2' => 'address2',
                    'donorCity' => 'city',
                    'donorProvince' => 'province',
                    'donorPostalCode' => 'postalCode',
                    'donorPhone' => 'phone',
                    'total' => 'SUM(Item.fairRetailPrice)'
                )
            );
            $select->group('Item.donorId');
            $select->order('Item.controlSource');

            $items  = $table->fetchAll($select);

            foreach ($items as $item) {

                $row = array(
                    $item->controlSource,
                    $item->donorCompany,
                    $item->donorFirstName,
                    $item->donorLastName,
                    $item->donorAddress1,
                    $item->donorAddress2,
                    $item->donorCity,
                    $item->donorProvince,
                    $item->donorPostalCode,
                    $item->donorPhone,
                    $item->total
                );

                fputcsv($fp, $row);
            }

            fclose($fp);

            $this->_redirector->gotoUrl('/exports/donor_totals_' .  $this->view->getActiveAuctionDate(1) . '.csv');
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
