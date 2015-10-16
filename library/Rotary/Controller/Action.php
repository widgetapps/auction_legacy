<?php

require_once('DJP/Exception.php');
require_once('DJP/Controller/Action.php');
require_once('Zend/Session/Namespace.php');

class Rotary_Controller_Action extends DJP_Controller_Action
{
	
	protected $sessionNamespace;
	
	function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
	{
	    parent::__construct($request, $response, $invokeArgs);

	    $this->sessionNamespace = new Zend_Session_Namespace('module');
	    
        // Add the Auction view help path.
        $helperPath = DJP_Config::getInstance()->application_root
        . DIRECTORY_SEPARATOR . 'library'
        . DIRECTORY_SEPARATOR . 'Rotary'
        . DIRECTORY_SEPARATOR . 'View'
        . DIRECTORY_SEPARATOR . 'Helper';
        $this->view->addHelperPath($helperPath, 'Rotary_View_Helper');
        
        if ($this->auth->hasIdentity()){
            $this->view->role        = $this->auth->getIdentity()->role;
            $this->view->hasIdentity = true;
            $this->view->userId      = $this->auth->getIdentity()->userId;
        } else {
            $this->view->role        = 'guest';
            $this->view->hasIdentity = false;
        }
        
        $mconfig = array('auth' => 'login',
                'username' => 'darryl@eastyorkrotary.org',
                'password' => 'BEF9ufrUph',
        		'ssl'      => 'tls',
        		'port'     => '587');
        require_once 'Zend/Mail.php';
        require_once 'Zend/Mail/Transport/Smtp.php';
        $tr = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $mconfig);
        Zend_Mail::setDefaultTransport($tr);
	}
    
	public function setupAcl()
	{
	    $this->acl->addRole(new Zend_Acl_Role('bidder'));
	    $this->acl->addRole(new Zend_Acl_Role('rotarian'));
	    $this->acl->addRole(new Zend_Acl_Role('approver'));
	    $this->acl->addRole(new Zend_Acl_Role('admin'));
	    $this->acl->addRole(new Zend_Acl_Role('super'));
	    
	    $this->acl->add(new Zend_Acl_Resource('items'));
	    $this->acl->add(new Zend_Acl_Resource('bids'));
	    $this->acl->add(new Zend_Acl_Resource('people'));
	    $this->acl->add(new Zend_Acl_Resource('blocks'));
	    $this->acl->add(new Zend_Acl_Resource('callbacks'));
	    $this->acl->add(new Zend_Acl_Resource('pickups'));
	    $this->acl->add(new Zend_Acl_Resource('pledges'));
	    $this->acl->add(new Zend_Acl_Resource('users'));
	    
	    // Rights for the Bids module
	    $this->acl->allow('bidder', 'bids', array('view', 'add'));
	    $this->acl->allow('rotarian', 'bids', array('view', 'add', 'approve'));
	    $this->acl->allow('approver', 'bids', array('view', 'add', 'edit', 'approve'));
	    $this->acl->allow('admin', 'bids', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('super', 'bids', array('view', 'add', 'edit', 'approve', 'delete'));
	    
	    // rights for the Items module
	    $this->acl->deny('bidder', 'items', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('rotarian', 'items', array('view', 'add'));
	    $this->acl->allow('approver', 'items', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('admin', 'items', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('super', 'items', array('view', 'add', 'edit', 'approve', 'delete'));
	    
	    // Rights for the People module
	    $this->acl->deny('bidder', 'people', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('rotarian', 'people', array('view', 'add'));
	    $this->acl->allow('approver', 'people', array('view', 'add', 'edit', 'approve'));
	    $this->acl->allow('admin', 'people', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('super', 'people', array('view', 'add', 'edit', 'approve', 'delete'));
	    
	    // Rights for the Blocks module
	    $this->acl->deny('bidder', 'blocks', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('rotarian', 'blocks', array('view'));
	    $this->acl->allow('approver', 'blocks', array('view', 'add', 'edit', 'approve'));
	    $this->acl->allow('admin', 'blocks', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('super', 'blocks', array('view', 'add', 'edit', 'approve', 'delete'));
	    
	    // Rights for the Callbacks module
	    $this->acl->deny('bidder', 'callbacks', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('rotarian', 'callbacks', array('view'));
	    $this->acl->allow('approver', 'callbacks', array('view', 'add', 'edit', 'approve'));
	    $this->acl->allow('admin', 'callbacks', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('super', 'callbacks', array('view', 'add', 'edit', 'approve', 'delete'));
	    
	    // Rights for the Pickups module
	    $this->acl->deny('bidder', 'pickups', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('rotarian', 'pickups', array('view'));
	    $this->acl->allow('approver', 'pickups', array('view', 'add', 'edit', 'approve'));
	    $this->acl->allow('admin', 'pickups', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('super', 'pickups', array('view', 'add', 'edit', 'approve', 'delete'));
	    
	    // Rights for the Pledges module
	    $this->acl->deny('bidder', 'pledges', array('view', 'add'));
	    $this->acl->allow('rotarian', 'pledges', array('view', 'add'));
	    $this->acl->allow('approver', 'pledges', array('view', 'add', 'edit', 'approve'));
	    $this->acl->allow('admin', 'pledges', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('super', 'pledges', array('view', 'add', 'edit', 'approve', 'delete'));
	    
	    // Rights for the Users module
	    $this->acl->deny('bidder', 'users', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->deny('rotarian', 'users', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->deny('approver', 'users', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->deny('admin', 'users', array('view', 'add', 'edit', 'approve', 'delete'));
	    $this->acl->allow('super', 'users', array('view', 'add', 'edit', 'approve', 'delete'));
	}
	
	public function getProvinceArray()
	{
	    $provs = array(
	                'AB' => 'Alberta',
	                'BC' => 'British Columbia',
	                'MB' => 'Manitoba',
	                'NB' => 'New Brunswick',
	                'NL' => 'Newfoundland and Labrador',
	                'NT' => 'Northwest Territories',
	                'NS' => 'Nova Scotia',
	                'NU' => 'Nunavut',
	                'ON' => 'Ontario',
	                'PE' => 'Prince Edward Island',
	                'QC' => 'Quebec',
	                'SK' => 'Saskatchewan',
	                'YT' => 'Yukon');
	    return $provs;
	}
	
	public function getCurrentAuctionId()
	{
		if (isset($this->sessionNamespace->sessionAuctionId) && $this->sessionNamespace->sessionAuctionId > 0){
			return $this->sessionNamespace->sessionAuctionId;
		}
		return $this->config->currentAuctionId;
	}
}
