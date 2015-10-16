<?php

class Users_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'users';
    
    public function indexAction()
    {
        try {
        	$this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
        	$e->failed();
        }
    }
    
    public function listAction()
    {
    	try {
    		$this->authenticateAction('view');
    		
	        require_once('models/User.php');
	        $table = new models_User();
	        $this->view->users = $table->fetchAll();
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function addAction()
    {
    	try {
    		$this->authenticateAction('add');
    		
	        require_once('models/Person.php');
	        $table = new models_Person();
	        $select = $table->select()->where('canvasser = ?', 'y')->order('firstName');
	        $rows_person = $table->fetchAll($select);
	        
	        $rotarians = array();
	        
	        foreach ($rows_person as $person){
	        	$rotarians[$person->personId] = $person->firstName . ' ' . $person->lastName;
	        }
	        
	        $this->view->rotarians = $rotarians;
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function addprocessAction()
    {
    	try {
    		$this->authenticateAction('add');
        
	        require_once('models/User.php');
	        $table = new models_User();
	        
	        $data = array(
	                    'username' => $this->_getParam('login'),
	                    'personId' => $this->_getParam('personId'),
	                    'password_md5' => md5($this->_getParam('password')),
	                    'role'     => $this->_getParam('role')
	                );
	        
	        $userId = $table->insert($data);
	        
	        $this->_redirector->gotoUrl('/users/index/list');
    	} catch (Metis_Auth_Exceptoion $e) {
    		$e->failed();
    	}
    }
    
    public function editAction()
    {
    	try {
    		$this->authenticateAction('edit');
        
	        require_once('models/User.php');
	        $table = new models_User();
	        $this->view->user = $table->find($this->_getParam('id'))->current();
	        
	        require_once('models/Person.php');
	        $table = new models_Person();
	        $select = $table->select()->where('canvasser = ?', 'y')->order('firstName');
	        $rows_person = $table->fetchAll($select);
	        
	        $rotarians = array();
	        
	        foreach ($rows_person as $person){
	        	$rotarians[$person->personId] = $person->firstName . ' ' . $person->lastName;
	        }
	        
	        $this->view->rotarians = $rotarians;
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function editprocessAction()
    {
    	try {
    		$this->authenticateAction('edit');
        
	        require_once('models/User.php');
	        $table = new models_User();
	        
	        $data = array(
	                    'username' => $this->_getParam('login'),
	                    'personId' => $this->_getParam('personId'),
	                    'role'     => $this->_getParam('role')
	                );
	        if ($this->_getParam('password') != ''){
	            $data['password_md5'] = md5($this->_getParam('password'));
	        }
	        
	        $where = $table->getAdapter()->quoteInto('userId = ?', $this->_getParam('userId'));
	        
	        $table->update($data, $where);
	        
	        $this->_redirector->gotoUrl('/users/index/list');
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    public function deleteAction()
    {
    	try {
    		$this->authenticateAction('delete');
        
	        require_once('models/User.php');
	        $table = new models_User();
	        
	        $where = $table->getAdapter()->quoteInto('userId=?', $this->_getParam('id'));
	        
	        try {
	            $table->delete($where);
	        } catch (Exception $e){}
	        
	        $this->_redirector->gotoUrl('/users/index/list');
    	} catch (Metis_Auth_Exeption $e) {
    		$e->failed();
    	}
    }
    
    public function welcomeAction()
    {
    	try {
    		$this->authenticateAction('edit');
        
	        require_once('models/User.php');
	        $table = new models_User();
	        $user = $table->find($this->_getParam('id'))->current();
	        
	        require_once('models/Person.php');
	        $table_p = new models_Person();
	        $person = $table_p->find($user->personId)->current();
	        
	        $this->sendWelcomeEmail($person->email);
    	} catch (Metis_Auth_Exception $e) {
    		$e->failed();
    	}
    }
    
    private function sendWelcomeEmail($to)
    {
        $mail = new Zend_Mail();
		$mail->setBodyHtml($this->getWelcomeEmail());
		$mail->setFrom('admin@metrotorontorotaryauction.com', 'admin@metrotorontorotaryauction.com');
		$mail->addTo($to, $to);
		$mail->setSubject('[Rotary Auction] Welcome to the MTRA System!');
		
		$file_entry = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'MTRA_Item_Entry_Procedures.pdf';
    	$mail->createAttachment(file_get_contents($file_entry), 'application/pdf', Zend_Mime::DISPOSITION_INLINE , Zend_Mime::ENCODING_BASE64, $this->view->getActiveAuctionDate(1) . ' MTRA Item Entry Procedures.pdf');
    	
    	$file_warehouse = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'pdf' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'MTRA_Warehouse_Checkin_Procedures.pdf';
    	$mail->createAttachment(file_get_contents($file_warehouse), 'application/pdf', Zend_Mime::DISPOSITION_INLINE , Zend_Mime::ENCODING_BASE64, $this->view->getActiveAuctionDate(1) . ' MTRA Warehouse Check-in Procedures.pdf');
    	 
		try {
		    $mail->send();
		} catch (Exception $e){
			echo 'Problem sending email.';
		}
    }
    
    private function getWelcomeEmail()
    {
        $emailTemplate = new Zend_Layout();
        $scriptPath = APPLICATION_PATH
        . DIRECTORY_SEPARATOR . 'layouts'
        . DIRECTORY_SEPARATOR . 'emails';
        $emailTemplate->setLayoutPath($scriptPath);
        $emailTemplate->setLayout('welcomeemail');
        
        return $emailTemplate->render();
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
