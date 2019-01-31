<?php
class Sttl_Permissions_Block_Adminhtml_Permissions_Tab_Advanced extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    public function __construct()
    {
        parent::__construct();
		if(Mage::helper('permissions')->permissionsEnabled())
		{
			$this->setTemplate('sttl/permissions/advance_permissions.phtml');
			$this->_withProductCount = false;
		}	
    }
}
?>