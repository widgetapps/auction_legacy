<?php
require_once('DJP/View/Helper/DJP.php');

class Rotary_View_Helper_FormatPhoneNumber extends DJP_View_Helper_DJP
{
    public function formatPhoneNumber($phoneNumber)
    {
        return $phoneNumber . '(' . substr($phoneNumber, 0, 3) . ') ' . substr($phoneNumber, 3, 3) . '-' . substr($phoneNumber, 7, 4);
    }
}
