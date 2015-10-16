<?php
require_once ('Rotary/Point.php');

class Rotary_ItemLabel {
    
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
        $this->itemNumber    = new Rotary_Point();
        $this->blockNumber   = new Rotary_Point();
        $this->itemName      = new Rotary_Point();
        $this->itemDesc      = new Rotary_Point();
        $this->itemValue     = new Rotary_Point();
        $this->controlNumber = new Rotary_Point();
        $this->donor         = new Rotary_Point();
        $this->time          = new Rotary_Point();
        $this->bin           = new Rotary_Point();
        
        $this->startPoint    = new Rotary_Point($x, $y);
    }
    
    function getItemNumber()
    {
        return new Rotary_Point($this->itemNumber->getX() + $this->startPoint->getX(),
                                $this->itemNumber->getY() + $this->startPoint->getY());
    }
    
    function getBlockNumber()
    {
        return new Rotary_Point($this->blockNumber->getX() + $this->startPoint->getX(),
                                $this->blockNumber->getY() + $this->startPoint->getY());
    }
    
    function getItemName()
    {
        return new Rotary_Point($this->itemName->getX() + $this->startPoint->getX(),
                                $this->itemName->getY() + $this->startPoint->getY());
    }
    
    function getItemDesc()
    {
        return new Rotary_Point($this->itemDesc->getX() + $this->startPoint->getX(),
                                $this->itemDesc->getY() + $this->startPoint->getY());
    }
    
    function getItemValue()
    {
        return new Rotary_Point($this->itemValue->getX() + $this->startPoint->getX(),
                                $this->itemValue->getY() + $this->startPoint->getY());
    }
    
    function getControlNumber()
    {
        return new Rotary_Point($this->controlNumber->getX() + $this->startPoint->getX(),
                                $this->controlNumber->getY() + $this->startPoint->getY());
    }
    
    function getDonor()
    {
        return new Rotary_Point($this->donor->getX() + $this->startPoint->getX(),
                                $this->donor->getY() + $this->startPoint->getY());
    }
    
    function getTime()
    {
        return new Rotary_Point($this->time->getX() + $this->startPoint->getX(),
                                $this->time->getY() + $this->startPoint->getY());
    }
    
    function getBin()
    {
        return new Rotary_Point($this->bin->getX() + $this->startPoint->getX(),
                                $this->bin->getY() + $this->startPoint->getY());
    }
    
    function getStartPoint()
    {
        return $this->startPoint;
    }
    
    function setStartPoint(Rotary_Point $point)
    {
        $this->startPoint = $point;
    }
    
    function setupPoints(Rotary_Point $itemNumber, Rotary_Point $blockNumber, Rotary_Point $itemName, Rotary_Point $itemDesc, Rotary_Point $itemValue, Rotary_Point $controlNumber, Rotary_Point $donor, Rotary_Point $time, Rotary_Point $bin)
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