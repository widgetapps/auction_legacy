<?php

require_once('Zend/Db/Table/Abstract.php');

class models_vItemDetail extends Zend_Db_Table_Abstract
{
    protected $_name = 'vItemDetail';
    protected $_primary = 'itemId';
}
