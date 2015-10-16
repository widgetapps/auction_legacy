<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Block extends Zend_Db_Table_Abstract
{
    protected $_name = 'Block';
    protected $_primary = 'blockId';
    
    protected $_referenceMap = array(
        'Auction' => array(
            'columns'       => 'auctionId',
            'refTableClass' => 'models_Auction',
            'refColumns'    => 'auctionId'
        )
    );
}
