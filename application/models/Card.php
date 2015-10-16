<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Card extends Zend_Db_Table_Abstract
{
    protected $_name = 'Card';
    protected $_primary = 'cardId';
}
