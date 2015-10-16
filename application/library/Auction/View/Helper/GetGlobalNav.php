<?php

class Auction_View_Helper_GetGlobalNav extends Metis_View_Helper_Metis
{
    public function getGlobalNav()
    {
        return $this->view->render('_menu.phtml');
    }
}
