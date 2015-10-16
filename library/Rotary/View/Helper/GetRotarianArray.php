<?php
require_once('DJP/View/Helper/DJP.php');

class Rotary_View_Helper_GetRotarianArray extends DJP_View_Helper_DJP
{
    public function getRotarianArray()
    {
        $canvasserArray1 = array(
            'none' => 'Select Rotarian...'
        );
        $canvasserArray2 = array();
        
        require_once('models/Person.php');
        $table = new models_Person();
        $where = $table->getAdapter()->quoteInto('canvasser = ?', 'y');
        $rows  = $table->fetchAll($where);
        foreach ($rows as $person){
            if (!($person instanceof Zend_Db_Table_Row)){
                continue;
            }
            
            $fullName = $person->lastName . ', ' .$person->firstName;
            
            $canvasserArray2[(string)$person->personId] = $fullName;
        }
        asort($canvasserArray2);
        return $canvasserArray1 + $canvasserArray2;
    }
}
