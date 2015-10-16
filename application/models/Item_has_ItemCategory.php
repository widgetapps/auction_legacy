<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Item_has_ItemCategory extends Zend_Db_Table_Abstract
{
    protected $_name = 'Item_has_ItemCategory';
    protected $_primary = array('itemId', 'itemCategoryId');
    
    protected $_referenceMap = array(
        'Item' => array(
            'columns'       => 'itemId',
            'refTableClass' => 'models_Item',
            'refColumns'    => 'itemId'
        ),
        'ItemCategory' => array(
            'columns'       => 'itemCategoryId',
            'refTableClass' => 'models_ItemCategory',
            'refColumns'    => 'itemCategoryId'
        )
    );
}
