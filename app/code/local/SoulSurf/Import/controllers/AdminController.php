<?php 
class SoulSurf_Import_AdminController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_addContent($this->getLayout()
                            ->createBlock('import/index'))
            ->renderLayout();
    }
}
?>