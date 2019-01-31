<?php

class Sttl_Permissions_Block_Adminhtml_Sales_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
	protected function _prepareColumns()
	{
		parent::_prepareColumns();
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
			}	
    		if (count($storeViewIds) <=1 && isset($this->_columns['store_id']))
    		{
    		    unset($this->_columns['store_id']);
    		}
		}
		return $this;
	}
}