<?php

require_once('Zend/Db/Table/Abstract.php');

class models_Invoice_has_HowHear extends Zend_Db_Table_Abstract
{
    protected $_name = 'Invoice_has_HowHear';
    protected $_primary = array('howHearId', 'invoiceId');
    
    protected $_referenceMap = array(
        'HowHear' => array(
            'columns'       => 'howHearId',
            'refTableClass' => 'models_HowHear',
            'refColumns'    => 'howHearId'
        ),
        'Invoice' => array(
            'columns'       => 'invoiceId',
            'refTableClass' => 'models_Invoice',
            'refColumns'    => 'invoiceId'
        )
    );
}
