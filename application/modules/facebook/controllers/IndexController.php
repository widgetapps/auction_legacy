<?php
require_once('Facebook.php');

class Facebook_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'facebook';
    protected $facebook;
    protected $session;
    protected $loginUrl;
    
    public function init()
    {
    	parent::init();
    	
    	$this->_helper->layout->setlayout('facebook');
    	$this->facebook = new Facebook(array(
		  'appId'  => '134466619926785',
		  'secret' => 'f0c8084dead5cc77046cd6168735d93c',
		  'cookie' => true,
		));
		
			
	    $this->session = $this->facebook->getSession();
	    $this->loginUrl = $this->facebook->getLoginUrl(
	            array(
	            'canvas'    => 1,
	            'fbconnect' => 0,
	            'req_perms' => 'publish_stream',
	            'next' => 'http://apps.facebook.com/rotaryauction/'
	            )
	    );
	    $this->view->loginUrl = $this->loginUrl;
    }
    
    public function indexAction()
    {
	    
	    if (!$this->session){
	    	//$this->_redirector->gotoUrl('/facebook/index/login');
	    }
	    
        try {
            $this->view->uid  =   $this->facebook->getUser();
            $fbme =   $this->facebook->api('/me');
        } catch (FacebookApiException $e) {
            //$this->_redirector->gotoUrl('/facebook/index/login');
        }
        
        // Get the categories
        $availableCategories = array();
        $availableCategories[0] = 'All';
        require_once('models/ItemCategory.php');
        $table = new models_ItemCategory();
        $categories = $table->fetchAll(null, 'name');
        foreach($categories as $category){
            $availableCategories[$category->itemCategoryId] = $category->name;
        }
    		
        require_once('models/vItemList.php');
        $table = new models_vItemList();
        $select = $table->select(true)->setIntegrityCheck(false);
        
        $select->where('vItemList.auctionId = ?', $this->getCurrentAuctionId())
        		->where('vItemList.approved = ?', 'y')
        		->where('vItemList.publish = ?', 'y');
        
        $this->view->selectedCategory = '';
        if ($this->_getParam('type') == 'category' && $this->_getParam('category') > 0){
        	$select->join('Item_has_ItemCategory', 'Item_has_ItemCategory.itemId = vItemList.itemId', array('itemCategoryId'))
        	       ->where('Item_has_ItemCategory.itemCategoryId = ?', $this->_getParam('category'));
        	$this->view->searchType       = 'category';
        	$this->view->searchCriteria   = $availableCategories[$this->_getParam('category')];
        	$this->view->selectedCategory = $this->_getParam('category');
        }
        
        //echo $select->__toString();
        try {
        	$this->view->items = $table->fetchAll($select);
        } catch (Exception $e) {
        	var_dump($e);
        }
        $this->view->availableCategories = $availableCategories;
    }
    
    public function viewAction()
    {
    	require_once('OpenGraphTags.php');
        
        $table = new models_vItemDetail();
        $item = $table->find($this->_getParam('item'))->current();
        
        $ogtags = new OpenGraphTags(array(
        								'title' => $item->itemName . ' | ' . $this->view->getActiveAuctionDate(1) . ' Metro Toronto Live TV Auction',
        								'type'  => 'product',
        								'image' => 'https://system.metrotorontorotaryauction.com/images/ri_emblem_c_100x100.png',
        								'url'   => 'https://system.metrotorontorotaryauction.com/facebook/index/view/item/' . $item->itemId,
        								'site_name' => $this->view->getActiveAuctionDate(1) . ' Metro Toronto Live TV Auction',
        								'app_id'    => '134466619926785',
        								'description' => substr(trim($item->itemDescription), 0, 197) . "..."
        							));
        
        $this->view->item = $item;
        $this->view->title = $item->itemName . ' | ' . $this->view->getActiveAuctionDate(1) . ' Metro Toronto Live TV Auction';
        $this->view->ogtags = $ogtags->render();
        
    }
    
    public function loginAction()
    {
    	
    }

    public function noRouteAction()
    {
        $this->_redirect('/');
    }
}
