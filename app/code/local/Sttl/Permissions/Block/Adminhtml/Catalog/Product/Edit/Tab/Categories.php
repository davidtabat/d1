<?php

class Sttl_Permissions_Block_Adminhtml_Catalog_Product_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{
	public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
		if(Mage::helper('permissions')->permissionsEnabled())
		{
			$user = Mage::getSingleton('admin/session');
			$userId = $user->getUser()->getUserId();
			$roleId = Mage::getModel('admin/user')->load($userId)->getRole()->getRoleId();
			$currentRoles = Mage::getModel('permissions/advancedrole')->getCollection()->loadByRoleId($roleId);
			$coll = $currentRoles->getData();
			$errors = array_filter($coll);
			if (!empty($errors)) 
			{
				foreach($currentRoles as $r)
				{
					$gws = $r['gws_is_all'];
					$rootRoleIds = explode(',',$r['root_cat_ids']);
					$subRoleIds = explode(',',$r['sub_cat_ids']);
				}
				$allCategories = array_merge($rootRoleIds,$subRoleIds);
			}	
		}	
        if (is_null($collection)) 
        {
            $collection = Mage::getModel('catalog/category')->getCollection();

            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount($this->_withProductCount)
                ->setStoreId($storeId);
            if(Mage::helper('permissions')->permissionsEnabled())
			{
				if ($gws == 0)  
				{
					if (!empty($allCategories)) 
					{
						$collection->addIdFilter($allCategories);
					}
				}
			}	
	        $this->setData('category_collection', $collection);
        }
        return $collection;
    }
}
