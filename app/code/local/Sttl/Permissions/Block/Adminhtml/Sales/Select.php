<?php

class Sttl_Permissions_Block_Adminhtml_Sales_Select extends Mage_Adminhtml_Block_Sales_Order_Create_Store_Select
{
    public function getStoreCollection($group)
    {
        $stores = parent::getStoreCollection($group);
        if(Mage::helper('permissions')->permissionsEnabled())
		{
			$currentRoles = Mage::helper('permissions')->getUser();
			$coll = $currentRoles->getData();
			$errors = array_filter($coll);
			if (!empty($errors)) 
			{
				foreach($currentRoles as $r)
				{
					$storeViewIds = explode(',',$r['storeview_ids']);
				}
				$stores->addIdFilter($storeViewIds);
			}
		}	
        return $stores;
    }
} 