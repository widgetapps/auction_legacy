<?php

class Public_IndexController extends Auction_Controller_Action
{
    protected $moduleName = 'public';

    public function init()
    {
        parent::init();

        $this->_helper->layout->setlayout('publicresponsive');
    }


    public function indexAction()
    {

    }

    public function itemsAction()
    {
        require_once('models/vItemList.php');
        $table = new models_vItemList();

        $where = array();
        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
        $where[] = $table->getAdapter()->quoteInto('controlNumber > ?', 0);
        $where[] = $table->getAdapter()->quoteInto('blockNumber > ?', 0);
        $order = array('blockNumber', 'controlNumber');
        $this->view->items  = $table->fetchAll($where, $order);
        $this->view->userId = $this->auth->getIdentity()->userId;
    }

    public function itemdetailAction()
    {

    }
}
