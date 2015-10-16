<?php

class Auction_View_Helper_GetRotarianArray extends Metis_View_Helper_Metis
{
    public function getRotarianArray($orgId = null)
    {
        $canvasserArray = array();
        
        $table = new models_Person();
        $select = $table->select();
        $select->where('canvasser = ?', 'y');
        $select->order(array('lastName', 'firstName'));
        if ($orgId > 0) {
        	$select->where('organizationId = ?', $orgId);
        } else {
        	$canvasserArray['none'] = 'Select Rotarian...';
        }
        $rows  = $table->fetchAll($select);
        foreach ($rows as $person){
            if (!($person instanceof Zend_Db_Table_Row)){
                continue;
            }
            
            $fullName = $person->lastName . ', ' .$person->firstName;
            
            $canvasserArray[(string)$person->personId] = $fullName;
        }
        return $canvasserArray;
    }
}
