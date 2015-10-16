<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Auction extends Zend_Db_Table_Abstract
{
    protected $_name = 'Auction';
    protected $_primary = 'auctionId';
    
    protected $_referenceMap = array(
        'CurrentBlock' => array(
            'columns'       => 'currentBlockId',
            'refTableClass' => 'models_Block',
            'refColumns'    => 'blockId'
        )
    );
}
