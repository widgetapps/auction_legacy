<?php

class Auction_View_Helper_GetMinBidPercent extends Metis_View_Helper_Metis
{
	
    public function getMinBidPercent()
    {
    	
        $resources = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('resources');
        return $resources['appconfig']['minBidPercent'] / 100;
    }
}
