<?php

require_once('Zend/Db/Table/Abstract.php');

class models_User extends Zend_Db_Table_Abstract
{
    protected $_name = 'User';
    protected $_primary = 'userId';
    
    protected $_referenceMap = array(
        'Person' => array(
            'columns'       => 'personId',
            'refTableClass' => 'models_Person',
            'refColumns'    => 'personId'
        )
    );
}
