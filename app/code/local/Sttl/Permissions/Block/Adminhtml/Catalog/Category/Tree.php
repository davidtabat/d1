<?php
class Sttl_Permissions_Block_Adminhtml_Catalog_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
	public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
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
				$roleIds = array_merge($rootRoleIds,$subRoleIds);
			}	
		}	
		
        $collection = $this->getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('catalog/category')->getCollection();
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount($this->_withProductCount)
                ->setStoreId($storeId);
				if(Mage::helper('permissions')->permissionsEnabled())
				{
					$coll = $currentRoles->getData();
					$errors = array_filter($coll);
					if (!empty($errors)) 
					{
						if($gws == 0)
						{
							if (!empty($roleIds)) 
							{
								$collection->addIdFilter($roleIds);
							}
						}	
					}	
				}	
            $this->setData('category_collection', $collection);
        }
        return $collection;
    }
}