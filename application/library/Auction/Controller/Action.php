<?php

class Auction_Controller_Action extends Metis_Controller_Action
{
	
	protected $sessionNamespace;
	protected $resources;
	
	function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
	{
	    parent::__construct($request, $response, $invokeArgs);

	    $this->sessionNamespace = new Zend_Session_Namespace('module');
	    $this->resources = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('resources');
	    
        // Add the view helper path.
        $helperPath = APPLICATION_PATH
        . DIRECTORY_SEPARATOR . 'library'
        . DIRECTORY_SEPARATOR . 'Auction'
        . DIRECTORY_SEPARATOR . 'View'
        . DIRECTORY_SEPARATOR . 'Helper';
        $this->view->addHelperPath($helperPath, 'Auction_View_Helper');
        
        if ($this->auth->hasIdentity()){
            $this->view->role        = $this->auth->getIdentity()->role;
            $this->view->hasIdentity = true;
            $this->view->userId      = $this->auth->getIdentity()->userId;
        } else {
            $this->view->role        = 'guest';
            $this->view->hasIdentity = false;
        }
        
        setlocale(LC_ALL, 'en_US');
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
	
	public function getOrganizationArray()
	{
		$orgs = array();
		
        $table = new models_Organization();
        $select = $table->select()->where('`active` = ?', 'y');
        $rows = $table->fetchAll($select);
        
        foreach ($rows as $row) {
        	$orgs[$row->controlSource] = $row->name;
        }
        
        return $orgs;
	}
	
	public function getOrganizationId($cs)
	{
        $table = new models_Organization();
        $select = $table->select()->where('controlSource = ?', $cs);
        $row = $table->fetchRow($select);
        return $row->organizationId;
	}
	
	public function getUserInfo()
	{
		try {
	        if ($this->auth->hasIdentity()){
				$table = new models_User();
				$select = $table->select(Zend_Db_Table::SELECT_WITH_FROM_PART)->setIntegrityCheck(false);
				$select->from(array('a' => 'User'), array('userId', 'personId', 'username', 'role'))
							->joinLeft(array('b' => 'Person'), 'a.personId = b.personId', array('organizationId', 'firstName', 'lastName', 'companyName', 'address1', 'address2', 'city', 'province', 'country', 'postalCode', 'phone', 'fax', 'email', 'website', 'canvasser', 'donor'))
							->joinLeft(array('c' => 'Organization'), 'b.organizationId = c.organizationId', array('name AS organizationName', 'controlSource', 'type AS organizationType', 'active'))
							->where('a.userId=?', $this->auth->getIdentity()->userId);
				return $table->fetchRow($select);
	        }
		} catch (Exception $e) {
			return $e;
		}
	}
	
	public function getCurrentAuctionId()
	{
		if (isset($this->sessionNamespace->sessionAuctionId) && $this->sessionNamespace->sessionAuctionId > 0){
			return $this->sessionNamespace->sessionAuctionId;
		}
		return $this->resources['appconfig']['currentAuctionId'];
	}
	
	public function getMinBidPercent()
	{
		return $this->resources['appconfig']['minBidPercent'] / 100;
	}
}
