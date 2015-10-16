<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Invoice extends Zend_Db_Table_Abstract
{
    protected $_name = 'Invoice';
    protected $_primary = 'invoiceId';
    
    protected $_referenceMap = array(
        'Person' => array(
            'columns'       => 'personId',
            'refTableClass' => 'models_Person',
            'refColumns'    => 'personId'
        ),
        'Auction' => array(
            'columns'       => 'auctionId',
            'refTableClass' => 'models_Auction',
            'refColumns'    => 'auctionId'
        ),
        'Invoice_has_HowHear' => array(
            'columns'       => 'invoiceId',
            'refTableClass' => 'models_Invoice_has_HowHear',
            'refColumns'    => 'invoiceId'
        )
    );
}
