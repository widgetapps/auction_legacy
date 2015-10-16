<?php
require_once('DJP/View/Helper/DJP.php');

class Rotary_View_Helper_GetProvinceArray extends DJP_View_Helper_DJP
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
