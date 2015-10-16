<?php

require_once('Zend/Db/Table/Abstract.php');

class models_vItemWinner extends Zend_Db_Table_Abstract
{
    protected $_name = 'vItemWinner';
    protected $_primary = 'itemId';
}
