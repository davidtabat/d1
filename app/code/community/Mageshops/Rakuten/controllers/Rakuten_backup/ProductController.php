<?php
/**
 * @category    Mageshops
 * @package     Mageshops_Rakuten
 * @license     http://license.mageshops.com/  Unlimited Commercial License
 * @copyright   mageSHOPS.com 2014
 * @author      Taras Kapushchak with THANKS to mageSHOPS.com <info@mageshops.com>
 */

class Mageshops_Rakuten_Rakuten_ProductController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Rakuten Catalog'))
            ->_title($this->__('Manage Rakuten Products'));

        $this->loadLayout()->renderLayout();
    }

    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Get specified tab grid
     */
    public function gridOnlyAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('adminhtml/catalog_product_edit_tab_' . $this->getRequest()->getParam('gridOnlyBlock'))
                ->toHtml()
        );
    }

    public function massSyncToRakutenAction()
    {
        $productIds = $this->getRequest()->getParam('product');

        if (!is_array($productIds)) {

            $this->_getSession()->addError($this->__('Please select product(s).'));

        } else {

            if (!empty($productIds)) {
                try {
                    Mage::getSingleton('catalog/product_action')
                        ->updateAttributes($productIds, array('rakuten_sync' => 1), 0);

                    $notExportedProductIds = Mage::getModel('catalog/product')->getCollection()
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
                        ->addAttributeToFilter('rakuten_status', array(
                            array('neq' => 'exported'),
                            array('null' => true),
                            ), 'left')
                        ->getAllIds();

                    $toDeleteProductIds = Mage::getModel('catalog/product')->getCollection()
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToFilter('rakuten_sync', array('eq' => 1))
                        ->addAttributeToFilter('rakuten_status', array('eq' => 'to_delete'))
                        ->getAllIds();

                    Mage::getSingleton('catalog/product_action')
                        ->updateAttributes($notExportedProductIds, array('rakuten_status' => 'to_export'), 0)
                        ->updateAttributes($toDeleteProductIds, array('rakuten_status' => 'exported'), 0);

                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d product(s) will be synchronized.', count($productIds))
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }

        $this->_redirect('*/*');
    }

    public function massRemoveFromRakutenAction()
    {
        $productIds = $this->getRequest()->getParam('product');

        if (!is_array($productIds)) {

            $this->_getSession()->addError($this->__('Please select product(s).'));

        } else {

            if (!empty($productIds)) {
                try {
                    $toDeleteIds = Mage::getModel('catalog/product')->getCollection()
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToFilter('rakuten_status', array('eq' => 'exported'))
                        ->getAllIds();

                    foreach ($toDeleteIds as $key => $value) {
                        if (!in_array($value, $productIds)) {
                            unset($toDeleteIds[$key]);
                        }
                    }

                    Mage::getSingleton('catalog/product_action')
                        ->updateAttributes($productIds, array('rakuten_sync' => 0), 0)
                        ->updateAttributes($productIds, array('rakuten_status' => null), 0)
                        ->updateAttributes($toDeleteIds, array('rakuten_status' => 'to_delete'), 0);

                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d product(s) will be removed.', count($productIds))
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }

        $this->_redirect('*/*');
    }

    public function syncNowAction()
    {
        $productId = $this->getRequest()->getParam('entity_id', 0);

        try {

            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getRakutenSync()) {
                Mage::getModel('rakuten/product')->syncProductToRakuten($product);
                $product->setData('rakuten_status', 'exported')
                    ->getResource()
                    ->saveAttribute($product, 'rakuten_status');
                $this->_getSession()->addSuccess(
                    $this->__('Product was successfully synchronized to Rakuten [%s].', $product->getSku())
                );
            } else {
                Mage::getModel('rakuten/product')->deleteRakutenProduct($product->getSku());
                $productIds = array($product->getId());
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes($productIds, array('rakuten_id' => null), 0)
                    ->updateAttributes($productIds, array('rakuten_status' => null), 0)
                    ->updateAttributes($productIds, array('rakuten_variants' => null), 0)
                    ->updateAttributes($productIds, array('rakuten_variant_labels' => null), 0);
                $this->_getSession()->addSuccess(
                    $this->__('Product was successfully removed from Rakuten [%s].', $product->getSku())
                );
            }

        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }
}
