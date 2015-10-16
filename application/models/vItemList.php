<?php

require_once('Zend/Db/Table/Abstract.php');

class models_vItemList extends Zend_Db_Table_Abstract
{
    protected $_name = 'vItemList';
    protected $_primary = 'itemId';
}
