<?php

require_once('Zend/Db/Table/Abstract.php');

class models_ItemEbay extends Zend_Db_Table_Abstract
{
    protected $_name = 'ItemEbay';
    protected $_primary = 'itemEbayId';
    
    protected $_referenceMap = array(
        'Item' => array(
            'columns'       => 'itemId',
            'refTableClass' => 'models_Item',
            'refColumns'    => 'itemId'
        )
    );
}
