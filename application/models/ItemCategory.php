<?php

require_once('Zend/Db/Table/Abstract.php');

class models_ItemCategory extends Zend_Db_Table_Abstract
{
    protected $_name = 'ItemCategory';
    protected $_primary = 'itemCategoryId';
}
