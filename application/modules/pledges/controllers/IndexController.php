<?php

class Pledges_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'pledges';
    
    public function indexAction()
    {
    	try {
    		$this->authenticateAction('view');
	    		
	        require_once('models/Pledge.php');
	        require_once('models/Person.php');
	        
	        $table_pledge = new models_Pledge();
	        
	        $sql = 'SELECT A.pledgeId AS pledgeId, A.amount AS amount, B.personId AS personId, B.firstName AS firstName, B.lastName AS lastName, B.phone AS phone FROM Pledge A left join Person B using (personId)';
	        
	        $table_pledge->getAdapter()->setFetchMode(Zend_Db::FETCH_OBJ);
	        $this->view->pledges = $table_pledge->getAdapter()->fetchAll($sql);
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function newAction()
    {
        try {
        	$this->authenticateAction('add');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function newstep2Action()
    {
    	try {
    		$this->authenticateAction('add');
    		
	        require_once('models/Person.php');
	        $table = new models_Person();
	
	        $phone = $this->_getParam('phone1') . $this->_getParam('phone2') . $this->_getParam('phone3');
	        
			$select = $table->select();
			$select->from($table, array('firstName', 'lastName', 'phone', 'personId'))
			       ->where('phone = ?', $phone);
			
			$rows = $table->fetchAll($select);
			
	        $this->view->people = $rows;
        	$this->view->phone  = $phone;
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function newstep2processAction()
    {
    	try {
    		$this->authenticateAction('add');
    		
	        if ($this->_getParam('personId') == 'new'){
		        require_once('models/Person.php');
		        $table = new models_Person();
		        
		        $data = array(
		                    'firstName'   => $this->_getParam('firstName'),
		                    'lastName'    => $this->_getParam('lastName'),
		                    'phone'       => $this->_getParam('phone')
		                );
		                
		        $personId = $table->insert($data);
	        } else {
	            $personId = $this->_getParam('personId');
	        }
	        
	        require_once('models/Pledge.php');
	        $table_pledge = new models_Pledge();
		        
	        $data_pledge = array(
	                    'personId' => $personId,
	                    'amount'   => $this->_getParam('pledge'),
	                    'cause'    => 'breakfast'
	                );
	                
	        $pledgeId = $table_pledge->insert($data_pledge);
	        
	        $this->_redirect('/pledges');
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function deleteAction()
    {
    	try {
    		$this->authenticateAction('delete');
        
	        require_once('models/Pledge.php');
	        $table = new models_Pledge();
	        
	        $where = $table->getAdapter()->quoteInto('pledgeId=?', $this->_getParam('id'));
	        
	        try {
	            $table->delete($where);
	        } catch (Exception $e){}
	        
	        $this->_redirector->gotoUrl('/pledges');
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
