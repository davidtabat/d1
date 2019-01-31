<?php
class Sttl_Permissions_Model_Category extends Mage_Catalog_Model_Category
{
    protected function _beforeSave()
    {
        if (!$this->getId() AND !Mage::registry('sttl_category_is_new'))
        {
            Mage::register('sttl_category_is_new', true);
        }
        return parent::_beforeSave();
    }
    
    protected function _afterSave()
    {
        if(Mage::helper('permissions')->permissionsEnabled())
        {
			$currentRoles = Mage::helper('permissions')->getUser();
			$coll = $currentRoles->getData();
			$errors = array_filter($coll);
			if (!empty($errors)) 
			{
				foreach($currentRoles as $r)
				{
					$rootRoleIds = explode(',',$r['root_cat_ids']);
					$subRoleIds = explode(',',$r['sub_cat_ids']);
				}
				$roleIds = array_merge($rootRoleIds,$subRoleIds);
				if (!in_array($this->getId(), $roleIds)) 
				{
					$roleIds[] = $this->getId();
				}
				foreach($currentRoles as $ra)
				{
					$ra->setData('sub_cat_ids', implode(',', $roleIds));
                    $ra->save();
				}
			}
            if (true === Mage::registry('sttl_category_is_new'))
            {
                Mage::unregister('sttl_category_is_new');
                $this->setStoreId(0);
                //$this->setIsActive(false);
                //$this->save();
            }
        }
        return parent::_afterSave();
    }
}