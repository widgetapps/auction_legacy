<?php

class Auction_View_Helper_GetLoginName extends Metis_View_Helper_Metis
{
    public function getLoginName($userId, $getFullName = false)
    {
        require_once('models/User.php');
        require_once('models/Person.php');
        
        $table_user = new models_User();
        $user       = $table_user->find($userId)->current();
        
    	$displayName = '';
    	
    	if ($getFullName === true){
    		$table_person = new models_Person();
    		$person       = $table_person->find($user->personId)->current();
    		$displayName  = $person->firstName . ' ' . $person->lastName;
    	} else {
    		$displayName = $user->username;
    	}
    	
        return $displayName;
    }
}
