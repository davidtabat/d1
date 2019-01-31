<?php

class Sttl_Permissions_Model_System_Store extends Mage_Adminhtml_Model_System_Store
{
	public function __construct()
    {
		parent::__construct();
		if(Mage::helper('permissions')->permissionsEnabled())
		{
			$currentRoles = Mage::helper('permissions')->getUser();
			$coll = $currentRoles->getData();
			$errors = array_filter($coll);
			if (!empty($errors)) 
			{
				foreach($currentRoles as $r)
				{
					$gws = $r['gws_is_all'];
				}
				if ($gws == 0)
				{
					$this->setIsAdminScopeAllowed(false);
				}
			}	
		}	
    }
    
    protected function _loadWebsiteCollection()
    {
        $this->_websiteCollection = Mage::app()->getWebsites();
		if(Mage::helper('permissions')->permissionsEnabled())
		{
			$currentRoles = Mage::helper('permissions')->getUser();
			$coll = $currentRoles->getData();
			$errors = array_filter($coll);
			if (!empty($errors)) 
			{
				foreach($currentRoles as $r)
				{
					$website_id = explode(',',$r['website_id']);
				}
				if (!empty($website_id))
				{
					foreach ($this->_websiteCollection as $id => $website)
					{
						if (!in_array($id, $website_id))
						{
							unset($this->_websiteCollection[$id]);
						}
					}
				}
			}	
		}	
        return $this;
    }
    
    protected function _loadStoreCollection()
    {
        $this->_storeCollection = Mage::app()->getStores();
		if(Mage::helper('permissions')->permissionsEnabled())
		{
			$currentRoles = Mage::helper('permissions')->getUser();
			$coll = $currentRoles->getData();
			$errors = array_filter($coll);
			if (!empty($errors)) 
			{
				foreach($currentRoles as $r)
				{
					$storeview_ids = explode(',',$r['storeview_ids']);
				}
				if (!empty($storeview_ids))
				{
					foreach ($this->_storeCollection as $id => $store)
					{
						if (!in_array($id, $storeview_ids))
						{
							unset($this->_storeCollection[$id]);
						}
					}
				}
			}	
		}	
    }
}
