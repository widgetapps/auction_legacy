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
        $auctionId = $this->getCurrentAuctionId();

        $blockInfo = $this->getBlockInfo($auctionId);
        $blockEndInfo = $this->getNextBlockInfo($auctionId);

        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', explode(':', $blockEndInfo->startTime, -1));
    }

    public function bidboardAction()
    {
        $this->_helper->layout->setLayout('json');

        $auctionId = $this->getCurrentAuctionId();

        $blockInfo = $this->getBlockInfo($auctionId);
        $blockEndInfo = $this->getNextBlockInfo($auctionId);

        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', explode(':', $blockEndInfo->startTime, -1));
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
        $this->_helper->layout->setlayout('json');

        require_once('models/vItemDetail.php');
        $tItems = new models_vItemDetail();

        $this->view->item = $tItems->find($this->_getParam('id'))->current();
    }

    private function getItemsForBid($auctionId)
    {
        $table = new models_vOpenBlockItems();
        $where = array();
        $where[] = $table->getAdapter()->quoteInto('auctionId = ?', $auctionId);
        $where[] = $table->getAdapter()->quoteInto('blockId = ?', $this->getCurrentBlockId($auctionId));
        return $table->fetchAll($where, 'controlNumber');
    }
}
