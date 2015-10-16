<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Person extends Zend_Db_Table_Abstract
{
    protected $_name = 'Person';
    protected $_primary = 'personId';
    
    protected $_referenceMap = array(
        'Organization' => array(
            'columns'       => 'organizationId',
            'refTableClass' => 'models_Organization',
            'refColumns'    => 'organizationId'
        )
    );
}
