<?php
require_once ('Auction/Point.php');

class Auction_ItemLabel {
    
    private $itemNumber;
    private $blockNumber;
    private $itemName;
    private $itemDesc;
    private $itemValue;
    private $controlNumber;
    private $donor;
    private $time;
    
    private $startPoint;
    
    function __construct($x = 0, $y = 0)
    {
        $this->itemNumber    = new Auction_Point();
        $this->blockNumber   = new Auction_Point();
        $this->itemName      = new Auction_Point();
        $this->itemDesc      = new Auction_Point();
        $this->itemValue     = new Auction_Point();
        $this->controlNumber = new Auction_Point();
        $this->donor         = new Auction_Point();
        $this->time          = new Auction_Point();
        $this->bin           = new Auction_Point();
        
        $this->startPoint    = new Auction_Point($x, $y);
    }
    
    function getItemNumber()
    {
        return new Auction_Point($this->itemNumber->getX() + $this->startPoint->getX(),
                                $this->itemNumber->getY() + $this->startPoint->getY());
    }
    
    function getBlockNumber()
    {
        return new Auction_Point($this->blockNumber->getX() + $this->startPoint->getX(),
                                $this->blockNumber->getY() + $this->startPoint->getY());
    }
    
    function getItemName()
    {
        return new Auction_Point($this->itemName->getX() + $this->startPoint->getX(),
                                $this->itemName->getY() + $this->startPoint->getY());
    }
    
    function getItemDesc()
    {
        return new Auction_Point($this->itemDesc->getX() + $this->startPoint->getX(),
                                $this->itemDesc->getY() + $this->startPoint->getY());
    }
    
    function getItemValue()
    {
        return new Auction_Point($this->itemValue->getX() + $this->startPoint->getX(),
                                $this->itemValue->getY() + $this->startPoint->getY());
    }
    
    function getControlNumber()
    {
        return new Auction_Point($this->controlNumber->getX() + $this->startPoint->getX(),
                                $this->controlNumber->getY() + $this->startPoint->getY());
    }
    
    function getDonor()
    {
        return new Auction_Point($this->donor->getX() + $this->startPoint->getX(),
                                $this->donor->getY() + $this->startPoint->getY());
    }
    
    function getTime()
    {
        return new Auction_Point($this->time->getX() + $this->startPoint->getX(),
                                $this->time->getY() + $this->startPoint->getY());
    }
    
    function getBin()
    {
        return new Auction_Point($this->bin->getX() + $this->startPoint->getX(),
                                $this->bin->getY() + $this->startPoint->getY());
    }
    
    function getStartPoint()
    {
        return $this->startPoint;
    }
    
    function setStartPoint(Auction_Point $point)
    {
        $this->startPoint = $point;
    }
    
    function setupPoints(Auction_Point $itemNumber, Auction_Point $blockNumber, Auction_Point $itemName, Auction_Point $itemDesc, Auction_Point $itemValue, Auction_Point $controlNumber, Auction_Point $donor, Auction_Point $time, Auction_Point $bin)
    {
        $this->itemNumber    = $itemNumber;
        $this->blockNumber   = $blockNumber;
        $this->itemName      = $itemName;
        $this->itemDesc      = $itemDesc;
        $this->itemValue     = $itemValue;
        $this->controlNumber = $controlNumber;
        $this->donor         = $donor;
        $this->time          = $time;
        $this->bin			 = $bin;
    }
}
?>