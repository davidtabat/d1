<?php
class Sttl_Permissions_Block_Adminhtml_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher 
{
	public function __construct()
	{
		parent::__construct();
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
					$storeViewIds = explode(',',$r['storeview_ids']);
					$websiteId = explode(',',$r['website_id']);
				}
				if ($gws == 0) 
				{
					if (!empty($storeViewIds))
					{
						$this->hasDefaultOption(false);
					}
					if (!empty($websiteId))
					{
						$this->setWebsiteIds($websiteId);
					}
				}
			}	
		}	
	}
	
	public function getStores($group)
    {
		$stores = parent::getStores($group);
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
					$storeViewIds = explode(',',$r['storeview_ids']);
				}
				if($gws == 0)
				{
					if (!empty($storeViewIds))
					{
						foreach ($stores as $storeId => $store) 
						{
							if (!in_array($storeId, $storeViewIds)) 
							{
								unset($stores[$storeId]);
							}
						}
					}
				}	
			}	
		}	
        return $stores;
    }
	public function getStoreIds()
    {
        if (!isset($this->_storeIds)) 
        {
            $this->_storeIds = array();
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
						$storeViewIds = explode(',',$r['storeview_ids']);
					}
					if($gws == 0)
					{
						if (!empty($storeViewIds))
						{
							$this->_storeIds = array_merge($this->_storeIds, $storeViewIds);
						}
					}	
				}	
			}	
        }
        return $this->_storeIds;
    }
	protected function _toHtmlReports()
    {
        $permissionHtml = parent::_toHtml();
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
					$store_id = explode(',',$r['store_id']);
					$website_id = explode(',',$r['website_id']);
				}
				if ($gws == 0 && !empty($storeview_ids))
				{
					$flag = false;
					
					$CurrentWebsite  = Mage::app()->getRequest()->getParam('website');
					$CurrentStore    = Mage::app()->getRequest()->getParam('group');
					if (Mage::app()->getRequest()->getParam('store_ids'))
					{
						$CurrentStoreviews = explode(',', Mage::app()->getRequest()->getParam('store_ids'));
					} 
					else 
					{
						$CurrentStoreviews = array();
						if (Mage::app()->getRequest()->getParam('store'))
						{
							$CurrentStoreviews = array(Mage::app()->getRequest()->getParam('store'));
						}
					}
					
					if ($website_id && $store_id && $storeview_ids) 
					{
						if (in_array($CurrentWebsite, $website_id) || in_array($CurrentStore, $store_id) || array_intersect($CurrentStoreviews, $storeview_ids)) 
						{
							$flag = true;
						}
					}
					
					if (!$flag) 
					{
						$url = Mage::getModel('adminhtml/url');
						Mage::app()->getResponse()->setRedirect($url->getUrl('*/*/*', array('_current'=>false, 'store' => $storeview_ids[0])));
					}
				
					// removing <option value="">All Store Views</option> option if have limited access
					$permissionHtml = preg_replace('@<option value="">(.*)</option>@', '', $permissionHtml);
					
					// if no stores selected, need to select allowed
					if (!$CurrentWebsite && !$CurrentStore && !$CurrentStoreviews)
					{
						// enhanced switcher is used on categories page
						if (preg_match('@varienStoreSwitcher@', $permissionHtml))
						{
							$permissionHtml .= '
							<script type="text/javascript">
							try
							{
								Event.observe(window, "load", varienStoreSwitcher.optionOnChange);
							} catch (err) {}
							</script>
							';
						} 
						else
						{
							$permissionHtml .= '
							<script type="text/javascript">
							permissionsSwitchStore = function()
							{
								switchStore($("store_switcher"));
							}
							
							try
							{
								Event.observe(window, "load", permissionsSwitchStore);
							} catch (err) {}
							</script>
							';
						}
					}
				}
			}	
		}	
        return $permissionHtml;
    }
    
	protected function _toHtml()
    {
        if (strpos(Mage::app()->getRequest()->getControllerName(), 'report') !== false)
        {
            return $this->_toHtmlReports();
        }
        $permissionHtml = parent::_toHtml();
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
					$store_id = explode(',',$r['store_id']);
					$website_id = explode(',',$r['website_id']);
				}
				
				if ($gws == 0 && !empty($storeview_ids))
				{
					if (!empty($storeview_ids)) 
					{
						if (!in_array(Mage::app()->getRequest()->getParam('store'), $storeview_ids))
						{
							$url = Mage::getModel('adminhtml/url');
							Mage::app()->getResponse()->setRedirect($url->getUrl('*/*/*', array('_current'=>true, 'store'=>$storeview_ids[0])));
						}
					}
					$permissionHtml = preg_replace('@<option value="">(.*)</option>@', '', $permissionHtml);
				}
			}	
		}	
        if (preg_match('@varienStoreSwitcher@', $permissionHtml))
        {
            $permissionHtml .= '
            <script type="text/javascript">
            try
            {
                Event.observe(window, "load", varienStoreSwitcher.optionOnChange);
            } catch (err) {}
            </script>
            ';
        }
        return $permissionHtml;
    }
} 