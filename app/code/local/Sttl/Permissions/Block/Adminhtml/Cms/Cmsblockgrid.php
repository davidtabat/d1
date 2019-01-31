<?php
class Sttl_Permissions_Block_Adminhtml_Cms_Cmsblockgrid extends Mage_Adminhtml_Block_Cms_Block_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/block')->getCollection();
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
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
} 