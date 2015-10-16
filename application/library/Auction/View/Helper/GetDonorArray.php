<?php

class Auction_View_Helper_GetDonorArray extends Metis_View_Helper_Metis
{
    public function getDonorArray()
    {
        $donorArray1 = array(
            'none' => 'Select donor...'
        );
        $donorArray2 = array();
        
        require_once('models/Person.php');
        $table = new models_Person();
        $where = $table->getAdapter()->quoteInto('donor = ?', 'y');
        $rows  = $table->fetchAll($where);
        foreach ($rows as $person){
            if (!($person instanceof Zend_Db_Table_Row)){
                continue;
            }
            
            $fullName = '';
            
            if ($person->companyName != ''){
                $fullName = $person->companyName;
                if ($person->lastName != '' || $person->firstName != ''){
                    $fullName .= ' (' . $person->lastName . ', ' .$person->firstName . ')';
                }
            } else {
	            $fullName = $person->lastName . ', ' .$person->firstName;
            }
            
            $donorArray2[(string)$person->personId] = $fullName;
        }
        
        asort($donorArray2);
        return $donorArray1 + $donorArray2;
    }
}
