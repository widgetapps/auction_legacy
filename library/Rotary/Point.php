<?php
class Rotary_Point {
    private $x;
    private $y;
    
    function __construct($x = 0, $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }
    
    function getX()
    {
        return $this->x;
    }
    
    function getY()
    {
        return $this->y;
    }
    
    function setX($x)
    {
        $this->x = $x;
    }
    
    function setY($y)
    {
        $this->y = $y;
    }
}
?>