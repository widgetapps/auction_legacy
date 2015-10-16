<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Bid extends Zend_Db_Table_Abstract
{
    protected $_name = 'Bid';
    protected $_primary = 'bidId';
    
    protected $_referenceMap = array(
        'Person' => array(
            'columns'       => 'personId',
            'refTableClass' => 'models_Person',
            'refColumns'    => 'personId'
        )
    );
}
