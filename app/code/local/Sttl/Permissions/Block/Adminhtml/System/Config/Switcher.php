<?php

class Sttl_Permissions_Block_Adminhtml_System_Config_Switcher extends Mage_Adminhtml_Block_System_Config_Switcher
{
    public function getStoreSelectOptions()
    {
        $options = parent::getStoreSelectOptions();
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
					$storeview_ids = explode(',',$r['storeview_ids']);
					$website_id = explode(',',$r['website_id']);
				}
				if ($gws == 0 && !empty($storeview_ids) && !empty($website_id)) 
				{
					if (!empty($storeview_ids))
					{
						$currentStore        = Mage::getModel('core/store')->load($this->getRequest()->getParam('store'), 'code')->getId();
						if (!in_array($currentStore, $storeview_ids))
						{
							// redirecting to first allowed store
							$url         = Mage::getModel('adminhtml/url');
							$storeViewId = current($storeview_ids);
							$storeView   = Mage::getModel('core/store')->load($storeViewId);
							$website     = Mage::getModel('core/website')->load($storeView->getWebsiteId());

							Mage::app()->getResponse()->setRedirect($url->getUrl('*/*/*', array('store' => $storeView->getCode(), 'website' => $website->getCode())));
						}
					}

					if (!empty($website_id))
					{
						$currentWebsite  = Mage::getModel('core/website')->load($this->getRequest()->getParam('website'), 'code')->getId();

						if (!in_array($currentWebsite, $website_id)) 
						{
							// redirecting to first allowed website
							$url     = Mage::getModel('adminhtml/url');
							$website = Mage::getModel('core/website')->load(current($website_id));
							Mage::app()->getResponse()->setRedirect($url->getUrl('*/*/*', array('website' => $website->getCode())));
						}
					}
					unset($options['default']);
				}
			}	
		}	
        return $options;
    }
}
