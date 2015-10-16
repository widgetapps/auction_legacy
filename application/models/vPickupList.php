<?php

require_once('Zend/Db/Table/Abstract.php');

class models_vPickupList extends Zend_Db_Table_Abstract
{
    protected $_name = 'vPickupList';
    protected $_primary = 'itemId';
}
