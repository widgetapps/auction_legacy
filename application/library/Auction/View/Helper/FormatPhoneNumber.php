<?php

class Auction_View_Helper_FormatPhoneNumber extends Metis_View_Helper_Metis
{
    public function formatPhoneNumber($phoneNumber)
    {
        return '(' . substr($phoneNumber, 0, 3) . ') ' . substr($phoneNumber, 3, 3) . '-' . substr($phoneNumber, 6, 4);
    }
}
