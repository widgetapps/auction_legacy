<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Organization extends Zend_Db_Table_Abstract
{
    protected $_name = 'Organization';
    protected $_primary = 'organizationId';
}
