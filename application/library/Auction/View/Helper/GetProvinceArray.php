<?php

class Auction_View_Helper_GetProvinceArray extends Metis_View_Helper_Metis
{
    public function getProvinceArray()
    {
        $provinces = array(
            'none' => 'Select Province...',
            'AB' => 'ALBERTA',
            'BC' => 'BRITISH COLUMBIA',
            'MB' => 'MANITOBA',
            'NB' => 'NEW BRUNSWICK',
            'NL' => 'NEWFOUNDLAND and LABRADOR',
            'NS' => 'NOVA SCOTIA',
            'NT' => 'NORTHWEST TERRITORIES',
            'NU' => 'NUNAVUT',
            'ON' => 'ONTARIO',
            'PE' => 'PRINCE EDWARD ISLAND',
            'QC' => 'QUEBEC',
            'SK' => 'SASKATCHEWAN',
            'YT' => 'YUKON'
        );
        
        return $provinces;
    }
}
