<?php

class Auction_View_Helper_GetActiveAuctionDate extends Metis_View_Helper_Metis
{
	public static $YEAR        = 1;
	public static $MONTH       = 2;
	public static $DAY         = 3;
	public static $TIMESTAMP   = 4;
	
    public function getActiveAuctionDate($dateType = 4)
    {
    	$currentAuctionId = 0;
    	
        $resources = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('resources');
        if (isset($resources['appconfig']['currentAuctionId'])){
            $currentAuctionId = $resources['appconfig']['currentAuctionId'];
        }
    	
    	$sessionNamespace = new Zend_Session_Namespace('module');
    	if (isset($sessionNamespace->sessionAuctionId) && $sessionNamespace->sessionAuctionId > 0){
    		$currentAuctionId = $sessionNamespace->sessionAuctionId;
    	}
    	
        require_once('models/Auction.php');
        $table = new models_Auction();
        $row   = $table->find($currentAuctionId)->current();
        
        $time = strtotime($row->date);
        
        $dateString = '';
        
        switch ($dateType){
        	case Auction_View_Helper_GetActiveAuctionDate::$YEAR:
        		$dateString = date('Y', $time);
        		break;
        	case Auction_View_Helper_GetActiveAuctionDate::$MONTH:
        		$dateString = date('n', $time);
        		break;
        	case Auction_View_Helper_GetActiveAuctionDate::$DAY:
        		$dateString = date('j', $time);
        		break;
        	case Auction_View_Helper_GetActiveAuctionDate::$TIMESTAMP:
        	default:
        		return $time;
        }
        
        return $dateString;
    }
}
