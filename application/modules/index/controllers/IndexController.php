<?php

class IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'index';
    
    public function indexAction()
    {
        if ($this->auth->hasIdentity()){
        	if ($this->auth->getIdentity()->role == 'bidder') {
    			$this->_redirector->gotoUrl('/bids');
        	}
            $this->_redirector->gotoUrl('/index/index/home');
            return;
        }
    }
    
    public function homeAction()
    {
        if (!$this->auth->hasIdentity()){
            $this->_redirector->gotoUrl('/');
            return;
        }
        
        /*
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $resource = $bootstrap->getPluginResource('db');
        $db = $resource->getDbAdapter();
        
        $profiler = $db->getProfiler()->setEnabled(true);
        */
        
        $this->view->goal = 700;
        
        $table = new models_Item();
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)->setIntegrityCheck(false);
        
        $select->join(array('B' => 'Organization'), 'Item.controlSource = B.controlSource', array('itemCount' => 'COUNT(*)', 'name' => 'name', 'value' => 'SUM(fairRetailPrice)'))
               ->where('Item.auctionId = ?', $this->getCurrentAuctionId())
               ->group('Item.controlSource')
               ->order('itemCount DESC');
        try {
        	$this->view->leaderBoard = $table->fetchAll($select);
        	//$query = $profiler->getLastQueryProfile();
        	//echo $query->getQuery();
        } catch (Exception $e) {
        	$this->view->leaderBoard = 'error';
        	echo 'ERROR';
        }
        
        $table = new models_Item();
		$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)->setIntegrityCheck(false);
        
        $select->join(array('B' => 'Person'), 'Item.canvasserId = B.personId', array('itemCount' => 'COUNT(*)', 'firstName' => 'firstName', 'lastName' => 'lastName', 'companyName' => 'companyName', 'value' => 'SUM(fairRetailPrice)'))
               ->where('Item.auctionId = ?', $this->getCurrentAuctionId())
               ->group('B.personId')
               ->order('itemCount DESC')
               ->limit(10);
        try {
        	$this->view->rotarians = $table->fetchAll($select);
        } catch (Exception $e) {
        	$this->view->rotarians = 'error';
        	echo 'ERROR';
        }
        
        //$db->getProfiler()->setEnabled(false);
    }
    
    public function viewAction()
    {
        if ($this->auth->hasIdentity()){
            $this->_redirector->gotoUrl('/items');
            return;
        }
        
        $this->_helper->layout->setLayout('public');
        
    	require_once('OpenGraphTags.php');
        require_once('models/vItemList.php');
        
        $table = new models_vItemList();
        $item = $table->find($this->_getParam('item'))->current();
        
        $ogtags = new OpenGraphTags(array(
        								'title' => $item->name . " | Metro Toronto Live TV Auction",
        								'type'  => 'non_profit',
        								'image' => 'https://system.metrotorontorotaryauction.com/images/ri_emblem_c_100x100.png',
        								'url'   => 'https://system.metrotorontorotaryauction.com/facebook/index/view/item/' . $item->itemId,
        								'site_name' => 'Metro Toronto Live TV Auction',
        								'app_id'    => '134466619926785',
        								'description' => substr(trim($item->description), 0, 197) . "..."
        							));
        
        $this->view->item = $item;
        $this->view->title = $item->name . " | Metro Toronto Live TV Auction";
        $this->view->ogtags = $ogtags->render();
        
    }
    
    public function gettokenAction()
    {
        require_once('models/User.php');
        $table_user = new models_User();
        $select_user = $table_user->select()->where('username = ?', $this->_getParam('login'));
        $rows_user = $table_user->fetchAll($select_user);
        
        $found = false;
        
        if (count($rows_user) == 1){
        	$row_user = $rows_user->current();
        	
        	if ($row_user->personId > 0) {
				$found = true;
			
		        $table_person = new models_Person();
		        $row_person = $table_person->find($row_user->personId)->current();
		        
		        $mail = new Zend_Mail();
	        
				$mail->setBodyHtml($this->formatTokenEmail($this->generateLoginToken($row_user->username, $row_person->email), $this->getTokenExpiryString()));
				$mail->setFrom('admin@metrotorontorotaryauction.com', 'admin@metrotorontorotaryauction.com');
				$mail->addTo($row_person->email, $row_person->email);
				$mail->setSubject('[Rotary Auction] Login Request');
				
				try {
				    $mail->send();
				} catch (Exception $e){
				}
		        
        	}
        }
        
    	$this->view->found = $found;
    }
    
    public function loginAction()
    {
        if ($this->auth->hasIdentity()){
            $this->_redirector->gotoUrl('/index');
            return;
        }
        
        if ($this->_getParam('e') == 'y') {
        	$this->view->error = true;
        }
        
        $this->view->token = $this->_getParam('t');
    }
    
    public function loginprocessAction()
    {
        require_once('Zend/Auth/Adapter/DbTable.php');
        
        if ($this->auth->hasIdentity()){
            $this->render('alreadyLoggedIn');
            return;
        }
        
        // Validate The Token
        require_once('models/User.php');
        $table_user = new models_User();
        $select_user = $table_user->select()->where('username = ?', trim($this->_getParam('login')));
        $rows_user = $table_user->fetchAll($select_user);
        
        if (count($rows_user) == 1){
        	$row_user = $rows_user->current();
        	
        	if ($row_user->personId > 0) {
		        require_once('models/Person.php');
		        $table_person = new models_Person();
		        $row_person = $table_person->find($row_user->personId)->current();
		        
		        if (trim($this->_getParam('token')) != $this->generateLoginToken($row_user->username, $row_person->email)) {
		        	$this->_redirector->gotoUrl('/index/index/login/e/y');
		        	return;
		        }
        	}
        } else {
        	$this->_redirector->gotoUrl('/index/index/login/e/y');
        	return;
        }
        
        // Do the Zend_Auth thing
        $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table_Abstract::getDefaultAdapter());
        $authAdapter->setTableName('User')
                    ->setIdentityColumn('username')
                    ->setCredentialColumn('password_md5')
                    ->setCredentialTreatment('MD5(?)');
                     
		$authAdapter->setIdentity(trim($this->_getParam('login')))
		            ->setCredential(trim($this->_getParam('password')));
		
		// Perform the authentication query, saving the result
		$result = $authAdapter->authenticate();
		
		if (!$result->isValid()){
		    $this->_redirector->gotoUrl('/index/index/login/e/y');
		}
		
		$this->auth->getStorage()->write($authAdapter->getResultRowObject(null, 'password_md5'));
		
		if ($this->auth->getIdentity()->role == 'disabled') {
	        $this->auth->clearIdentity();
	        $this->_redirector->gotoUrl('/index');
	        return;
		}
        
        $failedLogin = new Zend_Session_Namespace('failedLogin');
        if (isset($failedLogin->redirect)){
            $this->_redirector->gotoUrl($failedLogin->redirect);
        }
		
        $this->_redirector->gotoUrl('/index');
    }
    
    public function logoutAction()
    {
        $this->auth->clearIdentity();
        $this->sessionNamespace->sessionAuctionId = null;
        $this->_redirector->gotoUrl('/index');
    }
    
    public function resetpasswordAction()
    {
    }
    
    public function resetpasswordprocessAction()
    {
		require_once 'Zend/Mail.php';
		
		$mail = new Zend_Mail();
		
		$email = $this->_getParam('emailaddress');
		
        require_once('models/Person.php');
        $table = new models_Person();
        $select = $table->select()->where('email = ?', $email);
        $rows = $table->fetchAll($select);
        
        if (count($rows) > 0){
        	$row = $rows->current();
        	
	        require_once('models/User.php');
	        $table_user = new models_User();
	        $select = $table_user->select()->where('personId = ?', $row->personId);
	        $user_rows = $table->fetchAll($select);
	        
	        if (count($user_rows) == 0){
	        	$this->view->found = false;
	        	return;
	        }
	        
	        $user = $user_rows->current();
	        
			$mail->setBodyHtml($this->formatResetEmail($email, $user->userId));
			$mail->setFrom('admin@metrotorontorotaryauction.com', 'admin@metrotorontorotaryauction.com');
			$mail->addTo($email, $email);
			$mail->setSubject('[Rotary Auction] Password Reset Request');
			
			try {
			    $mail->send();
			} catch (Exception $e){
				$this->view->senderror = true;
			}
			
        	$this->view->found = true;
        } else {
        	$this->view->found = false;
        	return;
        }
    	
    }
    
    public function resetpasswordconfirmAction()
    {
        $this->view->found = true;
        
        require_once('models/User.php');
        $table_user = new models_User();
        try {
        	$user = $table_user->find($this->_getParam('id'))->current();
        } catch (Exception $e){
        	$this->view->found = false;
        	return;
        }
        
        require_once('models/Person.php');
        $table_person = new models_Person();
        try {
        	$person = $table_person->find($user->personId)->current();
        } catch (Exception $e){
        	$this->view->found = false;
        	return;
        }
        
        if ($this->_getParam('key') != $this->generateResetKey($person->email, $user->userId)){
        	$this->view->found = false;
        	return;
        }
        
        $this->view->userId = $user->userId;
        $this->view->key    = $this->_getParam('key');
    }
    
    public function resetpasswordconfirmprocessAction()
    {
        $this->view->found = true;
        
        require_once('models/User.php');
        $table_user = new models_User();
        try {
        	$user = $table_user->find($this->_getParam('userId'))->current();
        } catch (Exception $e){
        	$this->view->found = false;
        	return;
        }
        
        require_once('models/Person.php');
        $table_person = new models_Person();
        try {
        	$person = $table_person->find($user->personId)->current();
        } catch (Exception $e){
        	$this->view->found = false;
        	return;
        }
        
        if ($this->_getParam('key') != $this->generateResetKey($person->email, $user->userId)){
        	$this->view->found = false;
        	return;
        }
        
        if ($this->_getParam('npass') != $this->_getParam('cpass')){
        	$this->view->found = false;
        	return;
        }
        $data = array();
        $data['password_md5'] = md5($this->_getParam('npass'));
        $where = $table_user->getAdapter()->quoteInto('userId = ?', $this->_getParam('userId'));
        $table_user->update($data, $where);
    }
    
    public function setupAcl()
    {
    	
    }
    
    public function failedauthAction()
    {
    	
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
    
    private function formatResetEmail($email, $userId)
    {
        $emailTemplate = new Zend_Layout();
        $scriptPath = APPLICATION_PATH
        . DIRECTORY_SEPARATOR . 'layouts'
        . DIRECTORY_SEPARATOR . 'emails';
        $emailTemplate->setLayoutPath($scriptPath);
        $emailTemplate->setLayout('resetpasswordemail');
        $emailTemplate->key    = $this->generateResetKey($email);
        $emailTemplate->userId = $userId;
        
        return $emailTemplate->render();
    }
    
    private function formatTokenEmail($token, $expiry)
    {
        $emailTemplate = new Zend_Layout();
        $scriptPath = APPLICATION_PATH
        . DIRECTORY_SEPARATOR . 'layouts'
        . DIRECTORY_SEPARATOR . 'emails';
        $emailTemplate->setLayoutPath($scriptPath);
        $emailTemplate->setLayout('gettokenemail');
        $emailTemplate->token  = $token;
        $emailTemplate->expiry = $expiry;
        
        return $emailTemplate->render();
    }
    
    private function generateResetKey($seed)
    {
    	return sha1('east' . $seed . 'york');
    }
    
    private function generateLoginToken($username, $email)
    {
    	$a = 'rotaryfred30';
    	$dateInfo = getdate();
    	$string = $dateInfo['mon'] . $email . $dateInfo['hours'] . $username . $dateInfo['mday'] . $a . $dateInfo['year'];
    	return sha1($string);
    }
    
    private function getTokenExpiryString()
    {
    	$dateInfo = getdate();
    	$dateInfo['hours'] += 1;
    	
    	return date('h:i:s A, F jS, Y', mktime($dateInfo['hours'], $dateInfo['minutes'], $dateInfo['seconds'], $dateInfo['mon'], $dateInfo['mday'], $dateInfo['year']));
    }
}
