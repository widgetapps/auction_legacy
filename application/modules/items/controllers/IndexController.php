<?php

class Items_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'items';
    
    public function indexAction()
    {
        try {
        	$this->authenticateAction('view');
        	$this->_redirector->gotoUrl('/items/index/itemlist');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function newitemAction()
    {
        try {
        	$this->authenticateAction('add');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function newitemstep1Action()
    {
        try {
        	$this->authenticateAction('add');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        	return;
        }
    }
    
    public function newitemstep1selectAction()
    {
        try {
        	$this->authenticateAction('add');
        	
	        require_once('models/Person.php');
	        $table = new models_Person();
	
			$select = $table->select();
			$select->from($table, array('MATCH (firstName, lastName, companyName) AGAINST ("' . $this->_getParam('donorName') . '") as rank', 'firstName', 'lastName', 'companyName', 'personId'))
			       ->where('donor = ?', 'y')
			       ->where('MATCH (firstName, lastName, companyName) AGAINST (?)', $this->_getParam('donorName'))
			       ->order('rank DESC');
			
			$rows = $table->fetchAll($select);
			
	        $this->view->people = $rows;
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function newitemstep1processAction()
    {
        try {
        	$this->authenticateAction('add');
	        if ($this->_getParam('donorId') == 'new'){
	            $this->_redirect('/items/index/newitemnewdonor');
	        } else {
	            $this->_redirect('/items/index/newitemstep2/donorId/' . $this->_getParam('donorId'));
	        }
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function newitemnewdonorAction()
    {
        try {
        	$this->authenticateAction('add');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function newitemnewdonorprocessAction()
    {
        try {
        	$this->authenticateAction('add');
        	
	        // Insert the donor, get the personId, and pass it on to step 2
	        if (
	            $this->_getParam('firstName') == '' ||
	            $this->_getParam('lastName') == '' ||
	            $this->_getParam('address1') == '' ||
	            $this->_getParam('city') == '' ||
	            $this->_getParam('email') == ''
	           ){
	            
	        }
	        
	        require_once('models/Person.php');
	        $table = new models_Person();
	        
	        $data = array(
	                    'firstName'   => $this->_getParam('firstName'),
	                    'lastName'    => $this->_getParam('lastName'),
	                    'companyName' => $this->_getParam('companyName'),
	                    'address1'    => $this->_getParam('address1'),
	                    'address2'    => $this->_getParam('address2'),
	                    'city'        => $this->_getParam('city'),
	                    'province'    => $this->_getParam('province'),
	                    'country'     => 'CA',
	                    'postalCode'  => $this->_getParam('postalCode1') . $this->_getParam('postalCode2'),
	                    'phone'       => $this->_getParam('phone1') . $this->_getParam('phone2') . $this->_getParam('phone3'),
	                    'email'       => $this->_getParam('email'),
	                    'website'     => $this->_getParam('website'),
	                    'canvasser'   => 'n',
	                    'donor'       => 'y'
	                );
	                
	        $personId = $table->insert($data);
	        
	        $this->_redirect('/items/index/newitemstep2/donorId/' . $personId);
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function newitemstep2Action()
    {
        try {
        	$this->authenticateAction('add');
	        $table = new models_Person();
	        $table_orgs = new models_Organization();
	        $select_orgs = $table_orgs->select()->order('name');
	        
	        $this->view->orgs  = $table_orgs->fetchAll($select_orgs);
	        $this->view->donor = $table->find($this->_getParam('donorId'))->current();
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function newitemstep2processAction()
    {
        try {
        	$this->authenticateAction('add');
        	
	        if ($this->_getParam('rotarianId') == 'none'){
	            $this->_redirect('/items/index/newitemstep2/donorId/' . $this->_getParam('donorId'));
	        } else {
	        
		        require_once('models/Person.php');
		        $table = new models_Person();
		        
		        $data = array(
		                    'firstName'   => $this->_getParam('firstName'),
		                    'lastName'    => $this->_getParam('lastName'),
		                    'companyName' => $this->_getParam('companyName'),
		                    'address1'    => $this->_getParam('address1'),
		                    'address2'    => $this->_getParam('address2'),
		                    'city'        => $this->_getParam('city'),
		                    'province'    => $this->_getParam('province'),
		                    'postalCode'  => $this->_getParam('postalCode1') . $this->_getParam('postalCode2'),
		                    'phone'       => $this->_getParam('phone1') . $this->_getParam('phone2') . $this->_getParam('phone3'),
		                    'email'       => $this->_getParam('email'),
	                    	'website'     => $this->_getParam('website')
		                );
		        $where = $table->getAdapter()->quoteInto('personId = ?', $this->_getParam('donorId'));
		        
		        $table->update($data, $where);
		        
	            $this->_redirect('/items/index/newitemstep3/donorId/' . $this->_getParam('donorId') . '/rotarianId/' . $this->_getParam('rotarianId'));
	        }
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function newitemstep3Action()
    {
    	try {
    		$this->authenticateAction('add');
	        require_once('models/Person.php');
	        $table = new models_Person();

	        $availableCategories = array();
	        require_once('models/ItemCategory.php');
	        $table_c = new models_ItemCategory();
	        $categories = $table_c->fetchAll(null, 'name');
	        foreach($categories as $category){
	            $availableCategories[$category->itemCategoryId] = $category->name;
	        }
	        
	        $this->view->categories = $availableCategories;
	        
	        $this->view->donor    = $table->find($this->_getParam('donorId'))->current();
	        $this->view->rotarian = $table->find($this->_getParam('rotarianId'))->current();
	        $this->view->orgs     = $this->getOrganizationArray();
    	} catch (Metis_Auth_Exeption $e) {
    		$e->failed();
    	}
    }
    
    public function newitemprocessAction()
    {
    	try {
    		$this->authenticateAction('add');
	    		
	        if (
	            $this->_getParam('itemName') == '' ||
	            $this->_getParam('itemDescription') == '' ||
	            $this->_getParam('itemValue') == '' ||
	            $this->_getParam('itemPieces') == '' ||
	            $this->_getParam('taxReceipt') == ''
	           ){
	            
	        }
	        
	        require_once('models/Item.php');
	        $table = new models_Item();
	        
	        $userInfo = $this->getUserInfo();
	        
	        $data = array(
	        			'userId'        => $this->auth->getIdentity()->userId,
	                    'controlSource'   => $userInfo->controlSource,
	                    //'controlNumber'   => $this->_getParam('controlNumber'),
	                    'name'            => $this->_getParam('itemName'),
	                    'description'     => $this->_getParam('itemDescription'),
	                    'notes'           => $this->_getParam('itemNotes'),
	                    'fairRetailPrice' => $this->_getParam('itemValue'),
	                    'taxReceipt'      => $this->_getParam('taxReceipt'),
	                    'canvasserId'     => $this->_getParam('rotarianId'),
	                    'donorId'         => $this->_getParam('donorId'),
	                    'numberOfPieces'  => $this->_getParam('itemPieces'),
	                    'auctionId'       => $this->getCurrentAuctionId(),
	                    'featureItem'     => 'n',
	                    'pickedUp'        => 'n',
	                    'publish'         => 'n',
	                    'approved'        => 'n',
	        			'anonymous'       => $this->_getParam('anonymous')
	                );
	        
	        if (in_array($this->auth->getIdentity()->role, array('admin', 'super'))){
	            $data['approved'] = 'y';
		        $data['controlSource'] = $this->_getParam('controlSource');
	        }
	        
	        if ($this->_getParam('itemCopies') > 0 && $this->_getParam('itemCopies') < 16) {
		        for ($i=0; $i < $this->_getParam('itemCopies'); $i++) {
			        $data['controlNumber'] = $this->getNextWebNumber($data['controlSource']);
			        $itemId = $table->insert($data);
		        }
	        } else {
		        $data['controlNumber'] = $this->getNextWebNumber($data['controlSource']);
		        $itemId = $table->insert($data);
	        }
	        
	        // Add the categories
	        foreach ($this->_getParam('cats') as $categoryId) {
	        	$this->addCategory($itemId, $categoryId);
	        }
	        
	        $this->_redirector->gotoUrl('/items/index/newitemdone/itemId/' . $itemId);
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function newitemdoneAction()
    {
        try {
        	$this->authenticateAction('add');
	        	
	        require_once('models/vItemDetail.php');
	        $table = new models_vItemDetail();
	        
	        $item = $table->find($this->_getParam('itemId'))->current();
	        
	        if (!in_array($this->auth->getIdentity()->role, array('admin', 'super'))){
				require_once 'Zend/Mail.php';
				$mail = new Zend_Mail();
				$mail->setBodyHtml($this->formatItemEmail($item));
				$mail->setFrom('admin@metrotorontorotaryauction.com', 'Posted by: ' . $this->auth->getIdentity()->username);
				$mail->addTo('admin@metrotorontorotaryauction.com', 'Auction');
				$mail->setSubject('[Rotary Auction] New Item Added');
				try {
				    $mail->send();
				} catch (Exception $e){}
	        }
	        
	        $this->view->item = $item;
	        $this->view->role = $this->auth->getIdentity()->role;
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function copyAction()
    {
    	try {
    		$this->authenticateAction('add');
    		
    		$userInfo = $this->getUserInfo();
    		
	    	$table_item = new models_Item();
	    	$row_item = $table_item->find($this->_getParam('id'))->current();
	    	
	    	$data = $row_item->toArray();
	    	
	    	unset($data['itemId']);
	    	$data['controlNumber'] = $this->getNextWebNumber($row_item->controlSource);
	    	
	    	$itemId = $table_item->insert($data);
	    	
	    	$this->_redirector->gotoUrl('/items/index/newitemdone/itemId/' . $itemId);
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function itemlistAction()
    {
    	try {
    		$this->authenticateAction('view');
    		
	        $table = new models_vItemList();
	        
	        $session_itemList = new Zend_Session_Namespace('itemList');
	        $order = $session_itemList->sort;
	        
	        switch ($this->_getParam('sort')){
	            case 'controlNumber':
	                $order = array('controlSource', 'controlNumber');
	                $session_itemList->sort = $order;
	                break;
	            case 'binNumber':
	                $order = 'binNumber';
	                $session_itemList->sort = $order;
	                break;
	            case 'itemNumber':
	                $order = 'itemNumber';
	                $session_itemList->sort = $order;
	                break;
	            case 'approved':
	                $order = 'approved';
	                $session_itemList->sort = $order;
	                break;
	            case 'value':
	                $order = 'fairRetailPrice';
	                $session_itemList->sort = $order;
	                break;
	            case 'publish':
	                $order = 'publish';
	                $session_itemList->sort = $order;
	                break;
	            case 'blockNumber':
	                $order = array();
	                $order[] = 'blockNumber';
	                $order[] = 'itemNumber';
	                $session_itemList->sort = $order;
	                break;
	        }
	        
	        $where = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
	        $this->view->items  = $table->fetchAll($where, $order);
        	$this->view->userId = $this->auth->getIdentity()->userId;
        
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }

	public function featurelistAction()
	{
		try {
			$this->authenticateAction('view');

			$table = new models_vItemList();

			$where = array();
			$where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
			$where[] = $table->getAdapter()->quoteInto('controlNumber > ?', 0);
			$where[] = $table->getAdapter()->quoteInto('blockNumber > ?', 0);
			$where[] = $table->getAdapter()->quoteInto('featureItem = ?', 'y');
			$order = array('blockNumber', 'controlNumber');

			$this->view->items  = $table->fetchAll($where, $order);
			$this->view->userId = $this->auth->getIdentity()->userId;

		} catch (Metis_Auth_Exception $e) {
			$e->failed();
		}


	}
    
    public function editAction()
    {
        require_once('models/Item.php');
        $table = new models_Item();
        $item = $table->find($this->_getParam('id'))->current();
        
    	try {
    		$this->authenticateAction('edit');
    	} catch (Metis_Auth_Exception $e) {
    		if ($item->userId != $this->auth->getIdentity()->userId) {
    			$e->failed();
    			return;
    		}
    	}
	   
    	$this->view->userId = $this->auth->getIdentity()->userId;
        $this->view->item   = $item;
        $this->view->orgs   = $this->getOrganizationArray();
    }
    
    public function editprocessAction()
    {
        require_once('models/Item.php');
        $table = new models_Item();
        $item = $table->find($this->_getParam('id'))->current();
        
    	try {
    		$this->authenticateAction('edit');
    	} catch (Metis_Auth_Exception $e) {
    		if ($item->userId != $this->auth->getIdentity()->userId) {
    			$e->failed();
    			return;
    		}
    	}
	    		
        if (
            $this->_getParam('itemName') == '' ||
            $this->_getParam('itemDescription') == '' ||
            $this->_getParam('itemValue') == '' ||
            $this->_getParam('itemPieces') == '' ||
            $this->_getParam('canvasser') == '' ||
            $this->_getParam('donor') == ''
           ){
            
        }
        
        require_once('models/Item.php');
        $table = new models_Item();
        
        $data = array(
                    'name'            => $this->_getParam('itemName'),
                    'description'     => $this->_getParam('itemDescription'),
                    'fairRetailPrice' => $this->_getParam('itemValue'),
                    'taxReceipt'      => $this->_getParam('taxReceipt'),
                    'canvasserId'     => $this->_getParam('canvasser'),
                    'donorId'         => $this->_getParam('donor'),
                    'numberOfPieces'  => $this->_getParam('itemPieces'),
	        		'anonymous'       => $this->_getParam('anonymous'),
		        	'approved'        => $this->_getParam('approved'),
		        	'publish'         => $this->_getParam('publish'),
		        	'minimumBid'      => $this->_getParam('minimumBid')
                );
                
        if (in_array($this->auth->getIdentity()->role, array('admin', 'super'))){
        	$data['controlSource']  = $this->_getParam('controlSource');
        	$data['controlNumber']  = $this->_getParam('controlNumber');
        	$data['binNumber']      = $this->_getParam('binNumber');
        	$data['notes']          = $this->_getParam('itemNotes');
        	$data['featureItem']    = $this->_getParam('feature');
        }
        
        $where = $table->getAdapter()->quoteInto('itemId = ?', $this->_getParam('itemId'));
        
        $table->update($data, $where);
        
        $this->_redirector->gotoUrl('/items/index/itemlist#item_' . $this->_getParam('itemId'));
    }
    
    public function deleteAction()
    {
    	try {
    		$this->authenticateAction('delete');
	    		
	        require_once('models/Item.php');
	        $table = new models_Item();
	        $item = $table->find($this->_getParam('id'))->current();
	        $this->view->item = $item;
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function deleteprocessAction()
    {
    	try {
    		$this->authenticateAction('delete');
	    		
	        require_once('models/Item.php');
	        $table = new models_Item();
	        
	        $where = $table->getAdapter()->quoteInto('itemId=?', $this->_getParam('itemId'));
	        
	        try {
	            $table->delete($where);
	        } catch (Exception $e){}
	        
	        require_once('models/Item_has_ItemCategory.php');
	        $table = new models_Item_has_ItemCategory();
	        
	        $where = $table->getAdapter()->quoteInto('itemId=?', $this->_getParam('itemId'));
	        
	        try {
	            $table->delete($where);
	        } catch (Exception $e){}
	        
	        $this->_redirector->gotoUrl('/items/index/itemlist');
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function categoriesAction()
    {
    	try {
    		$this->authenticateAction('add');
	    		
	        $availableCategories = array();
	        $assignedCategories = array();
	        
	        require_once('models/Item.php');
	        $table = new models_Item();
	        $item = $table->find($this->_getParam('id'))->current();
	        $this->view->item = $item;
	        
	        require_once('models/ItemCategory.php');
	        $table = new models_ItemCategory();
	        $categories = $table->fetchAll(null, 'name');
	        foreach($categories as $category){
	            $availableCategories[$category->itemCategoryId] = $category->name;
	        }
	        
	        require_once('models/Item_has_ItemCategory.php');
	        $table = new models_Item_has_ItemCategory();
	        $where = $table->getAdapter()->quoteInto('itemId = ?', $item->itemId);
	        $categories  = $table->fetchAll($where);
	        foreach($categories as $category){
	            $assignedCategories[$category->itemCategoryId] = $availableCategories[$category->itemCategoryId];
	        }
	        
	        $this->view->item = $item;
	        $this->view->availableCategories = $availableCategories;
	        $this->view->assignedCategories = $assignedCategories;
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function categoriesprocessAction()
    {
    	try {
    		$this->authenticateAction('add');
    		
	        if ($this->_getParam('clickAction') == 'add'){
	            $this->addCategory($this->_getParam('id'), $this->_getParam('availableCategories'));
	        } else if ($this->_getParam('clickAction') == 'remove') {
	            $this->removeCategory($this->_getParam('id'), $this->_getParam('assignedCategories'));
	        }
	        
	        $this->_redirector->gotoUrl('/items/index/categories/id/' . $this->_getParam('id'));
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function assignAction()
    {
    	try {
    		$this->authenticateAction('edit');
        
	        require_once('models/Item.php');
	        $table = new models_Item();
	        $item = $table->find($this->_getParam('id'))->current();
	        $this->view->item = $item;
	        
	        $this->view->blockArray = $this->getBlockArray();
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function assignprocessAction()
    {
    	try {
    		$this->authenticateAction('edit');
	    		
	        require_once('models/Item.php');
	        $table = new models_Item();
	        
	        $data = array(
	                    'blockId'    => $this->_getParam('blockId')
	                );
	        $where = $table->getAdapter()->quoteInto('itemId = ?', $this->_getParam('itemId'));
	        
	        $table->update($data, $where);
	        
	        $this->_redirector->gotoUrl('/items/index/itemlist#item_' . $this->_getParam('itemId'));
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }

    public function ebayAction()
    {
        require_once('models/Item.php');
        require_once('models/ItemEbay.php');

        $table_item = new models_Item();
        $item = $table_item->find($this->_getParam('id'))->current();

        $table_itemEbay = new models_ItemEbay();
        $select = $table_itemEbay->select();
        $select->from($table_itemEbay)
            ->where('itemId = ?', $item->itemId);
        $rows = $table_itemEbay->fetchAll($select);

        $ebay = '';
        if ($rows->count() == 0) {
            $ebay->ebayItemId = 0;
            $ebay->itemId = $item->itemId;
            $ebay->upc = '';
            $ebay->weight = '';
            $ebay->height = '';
            $ebay->width = '';
            $ebay->length = '';
            $ebay->condition = '';
        } else {
            $ebay = $rows->current();
        }

        try {
            $this->authenticateAction('edit');
        } catch (Metis_Auth_Exception $e) {
            if ($item->userId != $this->auth->getIdentity()->userId) {
                $e->failed();
                return;
            }
        }

        $this->view->userId = $this->auth->getIdentity()->userId;
        $this->view->item   = $item;
        $this->view->ebay   = $ebay;
    }

    public function ebayprocessAction()
    {

    }
    
    private function addCategory($itemId, $categoryId)
    {
        require_once('models/Item_has_ItemCategory.php');
        $table = new models_Item_has_ItemCategory();
        
        $data = array(
                    'itemId'         => $itemId,
                    'itemCategoryId' => $categoryId
                );
        
        try {
            $table->insert($data);
        } catch (Exception $e){}
    }
    
    private function removeCategory($itemId, $categoryId)
    {
        require_once('models/Item_has_ItemCategory.php');
        $table = new models_Item_has_ItemCategory();
        
        $where = array();
        
        $where[] = $table->getAdapter()->quoteInto('itemId=?', $itemId);
        $where[] = $table->getAdapter()->quoteInto('itemCategoryId=?', $categoryId);
        
        try {
            $table->delete($where);
        } catch (Exception $e){}
    }

    private function getBlockArray()
    {
        require_once('models/Block.php');
        $table = new models_Block();
        $where = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
        $blocks  = $table->fetchAll($where, 'number');
        
        $blockArray = array();
        foreach ($blocks as $block){
            $blockArray[$block->blockId] = $block->number;
        }
        
        return $blockArray;
    }
    
    private function getNextWebNumber($controlSource = 'S')
    {
        require_once('models/Item.php');
        $table = new models_Item();
        
        $where   = array();
        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
        // $where[] = $table->getAdapter()->quoteInto('controlSource = ?', $controlSource);
        $order = 'controlNumber DESC';
        
        $rowset  = $table->fetchAll($where, $order);
        
        if (count($rowset) > 0){
	        $row = $rowset->current();
	        return (int)$row->controlNumber + 1;
        }
        
        return 1;
    }
    
    private function formatItemEmail($item)
    {
        $emailTemplate = new Zend_Layout();
        $scriptPath = APPLICATION_PATH
        . DIRECTORY_SEPARATOR . 'layouts'
        . DIRECTORY_SEPARATOR . 'emails';
        $emailTemplate->setLayoutPath($scriptPath);
        $emailTemplate->setLayout('itememail');
        $emailTemplate->item = $item;
        
        return $emailTemplate->render();
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
