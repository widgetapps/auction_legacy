<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Item extends Zend_Db_Table_Abstract
{
    protected $_name = 'Item';
    protected $_primary = 'itemId';
    
    protected $_referenceMap = array(
        'Block' => array(
            'columns'       => 'blockId',
            'refTableClass' => 'models_Block',
            'refColumns'    => 'blockId'
        ),
        'Donor' => array(
            'columns'       => 'donorId',
            'refTableClass' => 'models_Donor',
            'refColumns'    => 'donorId'
        ),
        'Canvasser' => array(
            'columns'       => 'canvasserId',
            'refTableClass' => 'models_Canvasser',
            'refColumns'    => 'canvasserId'
        ),
        'Auction' => array(
            'columns'       => 'auctionId',
            'refTableClass' => 'models_Auction',
            'refColumns'    => 'auctionId'
        )
    );
}
