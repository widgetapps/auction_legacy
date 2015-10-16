<?php

require_once('Zend/Db/Table/Abstract.php');

class models_HowHear extends Zend_Db_Table_Abstract
{
    protected $_name = 'HowHear';
    protected $_primary = 'howHearId';
    
    protected $_referenceMap = array(
        'Invoice_has_HowHear' => array(
            'columns'       => 'howHearId',
            'refTableClass' => 'models_HowHear',
            'refColumns'    => 'howHearId'
        )
    );
}
