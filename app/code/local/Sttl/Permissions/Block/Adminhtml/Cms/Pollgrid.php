<?php
class Sttl_Permissions_Block_Adminhtml_Cms_Pollgrid extends Mage_Adminhtml_Block_Poll_Grid
{
    protected function _prepareCollection()
    {
		$collection = Mage::getModel('poll/poll')->getCollection();
        
		if(Mage::helper('permissions')->permissionsEnabled())
		{
			$currentRoles = Mage::helper('permissions')->getUser();
			$coll = $currentRoles->getData();
			$errors = array_filter($coll);
			if (!empty($errors)) 
			{
				foreach($currentRoles as $r)
				{
					if(!empty($r['storeview_ids']))
					{
						$storeViewIds = explode(',',$r['storeview_ids']);
					}
					else
					{
						$storeViewIds = '';
					}	
				}
				if(!empty($storeViewIds))
				{
					$collection->addStoreFilter($storeViewIds);
				}	
			}
		}
		
        $this->setCollection($collection);
        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();

        if (!Mage::app()->isSingleStoreMode()) {
            $this->getCollection()->addStoreData();
        }
        return $this;
	
    }
} 