<?php

class Auction_View_Helper_GetOrganizationInfo extends Metis_View_Helper_Metis
{
	public function getOrganizationInfo($orgId)
	{
        $table = new models_Organization();
        return $table->find($orgId)->current();
	}
}