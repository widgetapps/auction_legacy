<?php

class Items_ReportsController extends Auction_Controller_Action
{
    protected $moduleName = 'items';

    public function indexAction()
    {
        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }
    }

    public function bidsAction()
    {
        $this->_helper->layout->setLayout('reports');

        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }
    }

    public function bidsdataAction()
    {
        header('Content-type: application/json');
        $this->_helper->layout->setLayout('json');

        try {
            $this->authenticateAction('view');

            require_once('models/Block.php');
            $tBlock = new models_Block();
            require_once('models/Item.php');
            $tItem = new models_Item();
            require_once('models/Bid.php');
            $tBid = new models_Bid();

            $bwhere = $tBlock->getAdapter()->quoteInto('auctionId = ?', $this->getCurrentAuctionId());
            $border = 'number';
            $blocks  = $tBlock->fetchAll($bwhere, $border);

            $blockNumbers = [];
            $blockBidCount = [];

            foreach ($blocks as $block) {
                $bidCount = 0;
                $iwhere = $tItem->getAdapter()->quoteInto('blockId = ?', $block->blockId);
                $items = $tItem->fetchAll($iwhere);

                foreach ($items as $item) {
                    $bidWhere = $tBid->getAdapter()->quoteInto('itemId = ?', $item->itemId);
                    $bids = $tBid->fetchAll($bidWhere);
                    $bidCount += count($bids);
                }

                $blockNumbers[] = $block->number;
                $blockBidCount[] = $bidCount;
            }

            $this->view->blocks = $blockNumbers;
            $this->view->bidCount = $blockBidCount;

        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }
    }

    public function valueAction()
    {
        $this->_helper->layout->setLayout('reports');

        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }
    }

    public function pvalueAction()
    {
        $this->_helper->layout->setLayout('reports');

        try {
            $this->authenticateAction('view');
        } catch (Metis_Auth_Exception $e) {
            $e->failed();
        }
    }
}
