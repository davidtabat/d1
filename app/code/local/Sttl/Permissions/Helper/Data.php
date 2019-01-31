<?php
class Sttl_Permissions_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_PERMISSIONS_URL = 'sttl_tab/sttlpermissions/enabled';
	
    public function permissionsEnabled()
    {
        return Mage::getStoreConfig(self::XML_PATH_PERMISSIONS_URL);
    }
	
	public function getUser()
	{
		$user = Mage::getSingleton('admin/session');
		$userId = $user->getUser()->getUserId();
		$roleId = Mage::getModel('admin/user')->load($userId)->getRole()->getRoleId();
		$currentRoles = Mage::getModel('permissions/advancedrole')->getCollection()->loadByRoleId($roleId);
		return $currentRoles;
	}
	
	public function getFormActionUrl()
    {
		return Mage::helper("adminhtml")->getUrl('permissions/adminhtml_index/submit', array('_secure' => true));
    }
	
	public function getMultiselectHtml($rootId)
	{
		$sub_cat = Mage::getModel('catalog/category')->load($rootId);
		$sub_cat = $sub_cat->getAllChildren();
		return $sub_cat;
	}
}