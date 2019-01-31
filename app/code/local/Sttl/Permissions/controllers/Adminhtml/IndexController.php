<?php
class Sttl_Permissions_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
	protected function _init()
	{
        $id = $this->getRequest()->getParam('rid');
	}
	
	public function indexAction()
	{
		$this->_init();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('permissions/adminhtml_permissions_tab_advanced')->toHtml()
        );
	}
	
    public function submitAction()
    {
		$id = $this->getRequest()->getParam('id');
		if($id){
			echo $multiSelectHtml = Mage::helper('permissions')->getMultiselectHtml($id);
		}else{
			echo '';
		}
    }
}