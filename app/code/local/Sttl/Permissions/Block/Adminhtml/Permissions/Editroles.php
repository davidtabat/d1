<?php
class Sttl_Permissions_Block_Adminhtml_Permissions_Editroles extends Mage_Adminhtml_Block_Permissions_Editroles
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if(Mage::helper('permissions')->permissionsEnabled())
		{
			$this->addTab('advanced', array(
				'label'     => Mage::helper('permissions')->__('Multi Admin with Category Management'),
				//'url'       => $this->getFormActionUrl(),
				'content'   => $this->getLayout()->createBlock('permissions/adminhtml_permissions_tab_advanced')->toHtml(),
				//'class'     => 'ajax',
			));
			return $this;
		}	
    }
	public function getFormActionUrl()
    {
		return Mage::helper("adminhtml")->getUrl("permissions/adminhtml_index/");
    }
}
?>