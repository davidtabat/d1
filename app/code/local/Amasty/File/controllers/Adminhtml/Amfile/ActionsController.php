<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */

class Amasty_File_Adminhtml_Amfile_ActionsController extends Mage_Adminhtml_Controller_Action
{
    public function clearAction()
    {
        $products = $this->getRequest()->getParam('product');

        if (!is_array($products)) {
            throw new Exception($this->__('Please select product(s)'));
        }

        $files = Mage::getResourceModel('amfile/file_collection')
            ->addFieldToFilter('product_id', array('in' => $products));

        foreach ($files as $file)
            $file->delete();

        $this->_getSession()->addSuccess($this->__('Attachments successfully deleted'));

        $this->_redirect('adminhtml/catalog_product/index');
    }

    public function copyAction()
    {
        $products = $this->getRequest()->getParam('product');
        $source = +$this->getRequest()->getParam('source');

        if (!is_array($products)) {
            throw new Exception($this->__('Please select product(s)'));
        }

        if (!$source) {
            throw new Exception($this->__('Please provide the value for the action'));
        }

        $files = Mage::getResourceModel('amfile/file_collection')
            ->addFieldToFilter('product_id', $source);

        foreach ($files as $file)
        {
            $storeValues = Mage::getResourceModel('amfile/store_collection')
                ->addFieldToFilter('file_id', $file->getId());

            $customerGroupValues = Mage::getResourceModel('amfile/storeCustomerGroup_collection')
                                       ->addFieldToFilter('file_id', $file->getId());
            foreach ($products as $product)
            {
                $file
                    ->unsFileId()
                    ->setProductId($product)
                    ->save();

                foreach ($storeValues as $store)
                {
                    $store
                        ->unsId()
                        ->setFileId($file->getId())
                        ->save();
                }
                foreach ($customerGroupValues as $groupValue) {
                    $groupValue
                        ->unsId()
                        ->setFileId($file->getId())
                        ->save();
                }
            }
        }

        $this->_getSession()->addSuccess($this->__('Attachments successfully duplicated'));

        $this->_redirect('adminhtml/catalog_product/index');
    }

    protected function _isAllowed()
    {
        return true;
    }

}
