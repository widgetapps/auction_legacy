<?php

class People_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'people';
    
    public function indexAction()
    {
    }
    
    public function listAction()
    {
    	try {
    		$this->authenticateAction('view');
	    		
	        require_once('models/Person.php');
	        $table = new models_Person();
	        $select = $table->select();
	        
	        $listType = $this->_getParam('type');
	        
	        switch ($this->_getParam('type')){
	            case 'rotarians':
	                $select->where('canvasser = ?', 'y');
	                break;
	            case 'donors':
	                $select->where('donor = ?', 'y');
	                break;
	            default:
	            	$listType = 'all';
	        }
	        
	        $session_personList = new Zend_Session_Namespace('personList');
	        $order = $session_personList->sort;
	        
	        switch ($this->_getParam('sort')){
	            case 'firstName':
	            case 'lastName':
	            case 'companyName':
	                $order = $this->_getParam('sort');
	                $session_personList->sort = $this->_getParam('sort');
	        }
	        
	        $select->order($order);
	        $this->view->people   = $table->fetchAll($select);
	        $this->view->listType = $listType;
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function deleteAction()
    {
    	try {
    		$this->authenticateAction('delete');
    		
	        require_once('models/Person.php');
	        $table = new models_Person();
	        $this->view->person     = $table->find($this->_getParam('id'))->current();
	        $this->view->personType = $this->_getParam('type');
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function deleteprocessAction()
    {
    	try {
    		$this->authenticateAction('delete');
	        
	        //If the user didn't pick a person to xfer to, go back to the list.
	        if ($this->_getParam('xferId') == 'none'){
	            $this->_redirector->gotoUrl('/people/index/list/type/' . $this->_getParam('type') . '#' . $this->_getParam('personId'));
	        }
	        
	        // Delete the person's record
	        require_once('models/Person.php');
	        $table = new models_Person();
	        $where = $table->getAdapter()->quoteInto('personId = ?', $this->_getParam('personId'));
	        try {
	            $table->delete($where);
	        } catch (Exception $e){
	            $this->_redirector->gotoUrl('/people/index/list/type/' . $this->_getParam('type'));
	        }
	        
	        // Delete the person's user record
	        require_once('models/User.php');
	        $table = new models_User();
	        $where = $table->getAdapter()->quoteInto('personId = ?', $this->_getParam('personId'));
	        try {
	            $table->delete($where);
	        } catch (Exception $e){}
	        
	        // Xfer the bid personId
	        require_once('models/Bid.php');
	        $table = new models_Bid();
	        $data = array('personId' => $this->_getParam('xferId'));
	        $where = $table->getAdapter()->quoteInto('personId = ?', $this->_getParam('personId'));
	        $table->update($data, $where);
	        
	        // Xfer the item canvasserId/donorId
	        require_once('models/Item.php');
	        $table = new models_Item();
	        switch($this->_getParam('type')){
	            case 'rotarians':
			        $data = array('canvasserId' => $this->_getParam('xferId'));
			        $where = $table->getAdapter()->quoteInto('canvasserId = ?', $this->_getParam('personId'));
			        break;
	            case 'donors':
	            default:
			        $data = array('donorId' => $this->_getParam('xferId'));
			        $where = $table->getAdapter()->quoteInto('donorId = ?', $this->_getParam('personId'));
	        }
	        $table->update($data, $where);
	        
	        $this->_redirector->gotoUrl('/people/index/list/type/' . $this->_getParam('type'));
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function editAction()
    {
    	try {
    		$this->authenticateAction('edit');
	    		
	        require_once('models/Person.php');
	        $table = new models_Person();
	        $this->view->person     = $table->find($this->_getParam('id'))->current();
	        $this->view->personType = $this->_getParam('type');
	        $this->view->orgs       = array_merge(array('0'=>'No Organization'), $this->getOrganizationArray());
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function editprocessAction()
    {
    	try {
    		$this->authenticateAction('edit');
	    		
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
	        
	        $orgId = ($this->_getParam('organization')=='0'?'0':$this->getOrganizationId($this->_getParam('organization')));
	        
	        $data = array(
	                    'firstName'   => $this->_getParam('firstName'),
	                    'lastName'    => $this->_getParam('lastName'),
	                    'companyName' => $this->_getParam('companyName'),
	                    'address1'    => $this->_getParam('address1'),
	                    'address2'    => $this->_getParam('address2'),
	                    'city'        => $this->_getParam('city'),
	                    'province'    => $this->_getParam('province'),
	                    'country'     => 'CA',
	                    'postalCode'  => $this->_getParam('postalCode'),
	                    'phone'       => $this->_getParam('phone'),
	                    'email'       => $this->_getParam('email'),
	                    'website'     => $this->_getParam('website'),
	                    'canvasser'   => $this->_getParam('rotarian'),
	                    'donor'       => $this->_getParam('donor'),
	        			'organizationId' => $orgId
	                );
	        $where = $table->getAdapter()->quoteInto('personId = ?', $this->_getParam('personId'));
	        
	        $personId = $table->update($data, $where);
	        
	        $this->_redirect('/people/index/list/type/' . $this->_getParam('type') . '#' . $this->_getParam('personId'));
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function addAction()
    {
    	try {
    		$this->authenticateAction('add');
        	$this->view->personType = $this->_getParam('type');
	        $this->view->orgs       = array_merge(array('0'=>'No Organization'), $this->getOrganizationArray());
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function addprocessAction()
    {
    	try {
    		$this->authenticateAction('add');
        
	        require_once('models/Person.php');
	        $table = new models_Person();
	        
	        $orgId = ($this->_getParam('organization')=='0'?'0':$this->getOrganizationId($this->_getParam('organization')));
	        
	        $data = array(
	                    'firstName'   => $this->_getParam('firstName'),
	                    'lastName'    => $this->_getParam('lastName'),
	                    'companyName' => $this->_getParam('companyName'),
	                    'address1'    => $this->_getParam('address1'),
	                    'address2'    => $this->_getParam('address2'),
	                    'city'        => $this->_getParam('city'),
	                    'province'    => $this->_getParam('province'),
	                    'postalCode'  => $this->_getParam('postalCode'),
	                    'phone'       => $this->_getParam('phone'),
	                    'email'       => $this->_getParam('email'),
	                    'website'     => $this->_getParam('website'),
	                    'donor'       => $this->_getParam('donor'),
	                    'canvasser'   => $this->_getParam('rotarian'),
	        			'organizationId' => $orgId
	                );
	        
	        $personId = $table->insert($data);
	        
	        $this->_redirect('/people/index/list/type/' . $this->_getParam('type') . '#' . $personId);
    	} catch (Metis_Auth_Exeption $e) {
    		$e->failed();
    	}
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
