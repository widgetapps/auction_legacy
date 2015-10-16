<?php
require_once('DJP/View/Helper/DJP.php');

class Rotary_View_Helper_GetGlobalNav extends DJP_View_Helper_DJP
{
    public function getGlobalNav()
    {
        return $this->view->render('_menu.phtml');
    }
}
