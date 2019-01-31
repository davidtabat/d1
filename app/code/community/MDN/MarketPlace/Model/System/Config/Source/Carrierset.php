<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Model_System_Config_Source_Carrierset extends Mage_Core_Model_Abstract {

    /**
     * Get all options
     * 
     * @return array 
     */
    public function getAllOptions() {
        if (!$this->_options) {

            $options = array();
            $carriers = Mage::getStoreConfig('carriers', 0);

            foreach ($carriers as $carrierKey => $item) {

                //skip shipping method if not enabled
                if (Mage::getStoreConfigFlag('carriers/' . $carrierKey . '/active') != 1)
                    continue;

                $instance = mage::getModel($item['model']);
                $code = $item['model'];
                if ($item['model']) {
                    $model = mage::getModel($item['model']);
                    $allowedMethods = $model->getAllowedMethods();
                    if ($allowedMethods) {
                        foreach ($allowedMethods as $methodKey => $method) {
                            $options[] = array('value' => $carrierKey . '_' . $methodKey,
                                'label' => $instance->getConfigData('title') . ' - ' . $method
                            );
                        }
                    }
                }
            }
            $this->_options = $options;
        }
        return $this->_options;
    }

    /**
     * To option array
     * 
     * @return array 
     */
    public function toOptionArray() {
        return $this->getAllOptions();
    }

}