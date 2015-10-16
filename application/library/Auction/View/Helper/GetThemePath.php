<?php

class Auction_View_Helper_GetThemePath extends Metis_View_Helper_Metis
{
	public function getThemePath($type='image')
	{
		$path = '/';
		
        $resources = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('resources');
        if (isset($resources['appconfig']['theme'])){
            $theme = $resources['appconfig']['theme'];
            $path .= 'themes/' . $theme . '/';
        }
        
        switch ($type) {
        	case 'css':
        		$path .= 'css/';
        		break;
        	case 'image':
        	default:
        		$path .= 'images/';
        		break;
        }
        
        return $path;
	}
}