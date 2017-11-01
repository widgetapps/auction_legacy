<?php

class Auction_View_Helper_GetConditionArray extends Metis_View_Helper_Metis
{
    public function getConditionArray()
    {
        $conditions = array(
            'none' => 'Select Condition...',
            'New with tags' => 'New with tags',
            'New without tags' => 'New without tags',
            'New with defects' => 'New with defects',
            'Pre-owned' => 'Pre-owned'
        );
        
        return $conditions;
    }
}
