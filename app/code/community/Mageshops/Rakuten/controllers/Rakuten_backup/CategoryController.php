<?php
/**
 * @category  	Mageshops
 * @package    	Mageshops_Rakuten
 * @license    	http://license.mageshops.com/  Unlimited Commercial License
 * @copyright 	mageSHOPS.com 2014
 * @author 	    Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Rakuten_CategoryController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Rakuten Catalog'))
            ->_title($this->__('Manage Rakuten Categories'));

        $this->loadLayout()->renderLayout();
    }

    public function syncCategoriesAction()
    {
        $categoryIds = $this->getRequest()->getPost('category_ids', false);

        try {
            if (!empty($categoryIds)) {

                $cats = explode(',', $categoryIds);
                $cats = array_unique($cats);
                Mage::getModel('rakuten/category')->updateSyncList($cats);

            }

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Categories will be synchronized on next run.'));

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }

    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('rakuten/catalog_category')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
}
