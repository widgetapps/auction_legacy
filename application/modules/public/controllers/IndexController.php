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

        $close = explode(':', $blockEndInfo->startTime, -1);
        $close[1] -= 2;
        $close[1] = str_pad($close[1], 2, '0', STR_PAD_LEFT);

        date_default_timezone_set('America/Toronto');

        list($shour, $sminute, $ssecond) = explode(':', $blockInfo->startTime);
        list($syear, $smonth, $sday) = explode('-', $blockInfo->blockDate);
        $ts_closetime = mktime($shour, $sminute, $ssecond, $smonth, $sday, $syear) + 600;

        $this->view->currentTime = date('G:i:s');
        $this->view->countdown   = $this->countdown($ts_closetime);

        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', $close);
    }

    public function bidboardAction()
    {
        $this->_helper->layout->setLayout('json');

        $auctionId = $this->getCurrentAuctionId();

        $blockInfo = $this->getBlockInfo($auctionId);
        $blockEndInfo = $this->getNextBlockInfo($auctionId);

        $close = explode(':', $blockEndInfo->startTime, -1);
        $close[1] -= 2;
        $close[1] = str_pad($close[1], 2, '0', STR_PAD_LEFT);

        date_default_timezone_set('America/Toronto');

        list($shour, $sminute, $ssecond) = explode(':', $blockInfo->startTime);
        list($syear, $smonth, $sday) = explode('-', $blockInfo->blockDate);
        $ts_closetime = mktime($shour, $sminute, $ssecond, $smonth, $sday, $syear) + 600;

        $this->view->currentTime = date('G:i:s');
        $this->view->countdown   = $this->countdown($ts_closetime);

        $this->view->items       = $this->getItemsForBid($auctionId);
        $this->view->blockNumber = $blockInfo->number;
        $this->view->startTime   = implode(':', explode(':', $blockInfo->startTime, -1));
        $this->view->endTime     = implode(':', $close);
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

    private function getBlockInfo($auctionId)
    {
        $table_block = new models_Block();
        return $table_block->find($this->getCurrentBlockId($auctionId))->current();
    }

    private function getNextBlockInfo($auctionId)
    {
        $table_block = new models_Block();
        return $table_block->find($this->getCurrentBlockId($auctionId)+1)->current();
    }

    private function getCurrentBlockId($auctionId = null) {
        require_once('models/Auction.php');
        $auction = new models_Auction();
        if ($auctionId != null) {
            $where = "auctionId = " . $auctionId;
        } else {
            $where = "auctionId = " . $this->getCurrentAuctionId();
        }
        $row = $auction->fetchRow($where);
        return $row->currentBlockId;

    }

    private function countdown($then) {
        $now = time();
        $till = $then-$now; // seconds until $then

        $minutes = floor($till / 60);
        $seconds = $till % 60;

        if ($seconds < 10){
            $seconds = '0' . $seconds;
        }

        $cd_string = $minutes . ':' . $seconds;

        return $cd_string;
    }
}
