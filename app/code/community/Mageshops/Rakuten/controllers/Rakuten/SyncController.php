<?php

/**
 * @category        Mageshops
 * @package         Mageshops_Rakuten
 * @license         http://license.mageshops.com/  Unlimited Commercial License
 * @copyright       mageSHOPS.com 2014
 * @author          Taras Kapushchak & Kristaps Rjabovs with THANKS to mageSHOPS.com <info@mageshops.com>
 */
class Mageshops_Rakuten_Rakuten_SyncController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Rakuten Synchronization'))
            ->_title($this->__('Rakuten Synchronization'));

        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('rakuten/sync_request_grid')->toHtml()
        );
    }

    public function mappingAction()
    {
        $this->_title($this->__('Rakuten Product Mapping'))
            ->_title($this->__('Rakuten Product Mapping'));

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save product attributes mapping data.
     *
     * Step 1: Save default attributes.
     * Step 2: Save variant attributes.
     * Step 3: Refresh config cache.
     */
    public function saveMappingAction()
    {
        $config = Mage::getModel('core/config');

        // Step 1
        $this->_saveDefaultAttributesData($config);

        // Step 2
        $this->_saveVariantAttributesData($config);

        // Step 3
        Mage::app()->getStore()->resetConfig();

        $this->_redirect('*/*/mapping');
    }

    /**
     * Save default product attributes mapping data.
     * @param Mage_Core_Model_Config $config
     */
    protected function _saveDefaultAttributesData($config)
    {
        $mappings = $this->getRequest()->getParam('attribute_mapping', array());
        if (empty($mappings))
            return;

        foreach ($mappings as $code => $value) {
            $config->saveConfig('nn_market/rakuten_product_mapping/' . $code, $value);
        }
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Mappings were successfully saved'));
    }

    /**
     * Save variant product attributes mapping data.
     * @param Mage_Core_Model_Config $config
     */
    protected function _saveVariantAttributesData($config)
    {
        $variantMappings = $this->getRequest()->getParam('variant_mapping', array());
        if (empty($variantMappings))
            return;

        $errorMappings = array();
        foreach ($variantMappings as $code => $value) {
            if (Mage::helper('core/string')->strlen($value) > 20) {
                $errorMappings[] = $value;
            } else {
                $config->saveConfig('nn_market/rakuten_variant_mappings/' . $code, $value);
            }
        }
        if (count($errorMappings) > 0) {
            $error = 'These values are too long. Please reduce them to 20 chars max: %s';
            Mage::getSingleton('adminhtml/session')->addError($this->__($error, implode(', ', $errorMappings)));
        }
        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Variant definitions were successfully saved'));
    }

    public function syncAction()
    {
        $action = $this->getRequest()->get('action', false);
        try {

            $msg = false;

            switch ($action) {

                case 'get_categories':
                    Mage::getModel('rakuten/category')->syncAllRakutenCategories();
                    $msg = $this->__('Category list was successfully fetched from your Rakuten account.');
                    break;

                case 'sync_categories':
                    Mage::getModel('rakuten/category')->syncAllToRakuten();
                    $msg = $this->__('All categories were successfully synchronized with your Rakuten account.');
                    break;

                case 'clear_lock':
                    Mage::helper('rakuten')->unlockSync();
                    break;

                default:
                    break;
            }

            if ($msg) {
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('*/*');
    }

    /**
     * Event for starting synchronization process.
     *
     * @param bool $continue Indicates whether to continue previous process or to try to start from the beginning.
     */
    public function startSyncAction($continue = false)
    {
        /** @var Mageshops_Rakuten_Helper_Data $helper */
        $helper = Mage::helper('rakuten');

        // Reset state
        $helper->resetSynchronizationState();

        $helper->startBackgroundProcess($continue);

        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode(array(
                'status' => 'success',
                'message' => $this->__('Synchronization started.'),
                'time' => time(),
            ))
        );
    }

    public function checkStateAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $lock = Mage::helper('rakuten')->getState();
        $lock['status'] = 'success';
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($lock)
        );
    }

    public function clearSyncAction()
    {
        Mage::helper('rakuten')->clearSync();
        $this->_redirect('*/*');
    }
    
    /**
     * Clear all products from Rakuten and synchronization data
     * 
     * @return void
     */
    public function clearAllAction()
    {
        try {
            Mage::getModel('rakuten/product')->clearAll();
        } catch (Exception $e) {
            $helper = Mage::helper('rakuten');
            $helper->syncExceptionLog($e);
            $helper->setState($helper->__('Error occurred during deleting all data: %s', $e->getMessage()), 0);
        }
        $this->_getSession()->addSuccess(
            $this->__('All products from Rakuten and synchronization data was successfully deleted.')
        );
    }

    public function massDeleteRequestAction()
    {
        $requestIds = $this->getRequest()->getParam('entity_id');

        if (!is_array($requestIds)) {
            $this->_getSession()->addError($this->__('Please select record(s).'));
        } else {

            if (!empty($requestIds)) {
                try {
                    $requests = Mage::getModel('rakuten/rakuten_request')->getCollection()
                        ->addFieldToFilter('entity_id', array('in' => $requestIds));
                    foreach ($requests as $request) {
                        $request->delete();
                    }

                    $this->_getSession()->addSuccess(
                        $this->__('%d records were successfully removed.', count($requestIds))
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }

        $this->_redirect('*/*');
    }

    public function updateRakutenCategoriesAction()
    {
        Mage::helper('rakuten')->updateRakutenCategories();

        $this->_getSession()->addSuccess(
            $this->__('Rakuten marketplace categories are updated successful.')
        );

        $this->_redirect('adminhtml/system_config/edit/section/nn_market');
    }

    protected function _isAllowed() {
        return true;
    }


}
