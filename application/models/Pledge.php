<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Pledge extends Zend_Db_Table_Abstract
{
    protected $_name = 'Pledge';
    protected $_primary = 'pledgeId';
    
    protected $_referenceMap = array(
        'Person' => array(
            'columns'       => 'personId',
            'refTableClass' => 'models_Person',
            'refColumns'    => 'personId'
        )
    );
}
