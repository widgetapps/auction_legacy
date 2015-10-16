<?php

require_once('Zend/Db/Table/Abstract.php');

class models_InvoiceItem extends Zend_Db_Table_Abstract
{
    protected $_name = 'InvoiceItem';
    protected $_primary = 'invoiceItemId';
    
    protected $_referenceMap = array(
        'Item' => array(
            'columns'       => 'itemId',
            'refTableClass' => 'models_Item',
            'refColumns'    => 'itemId'
        )
    );
}
