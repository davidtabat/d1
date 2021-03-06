<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced SEO Suite
 * @version   1.3.5
 * @build     1248
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */


/**
* This file is part of the Mirasvit_SeoFilter project.
*
* Mirasvit_SeoFilter is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License version 3 as
* published by the Free Software Foundation.
*
* This script is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*
* PHP version 5
*
* @category Mirasvit_SeoFilter
* @package Mirasvit_SeoFilter
* @author Michael Türk <tuerk@flagbit.de>
* @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
* @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
* @version 0.1.0
* @since 0.1.0
*/
/**
 * Resource model for rewrites for filterable attribute options.
 *
 * @category Mirasvit_SeoFilter
 * @package Mirasvit_SeoFilter
 * @author Michael Türk <tuerk@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 * @version 0.1.0
 * @since 0.1.0
 */
class Mirasvit_SeoFilter_Model_Rewrite extends Mage_Core_Model_Abstract
{
    /**
    * Constructor
    *
    */
    protected function _construct() {
        $this->_init('seofilter/rewrite');
    }

    /**
     * Loads current rewrite from given filter and the wanted option_id value.
     * This is triggered upon link generation - at this point in time we have exactly these information and need an
     * appropriate rewrite string.
     *
     * If no rewrite could be found in database, the model tries to create itself from the data of filter and option_id.
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter The current filter object.
     * @param int $optionId The given option_id value.
     * @return Mirasvit_SeoFilter_Model_Rewrite (Hopefully loaded) self.
     */
    public function loadByFilterOption(Mage_Catalog_Model_Layer_Filter_Attribute $filter, $optionId)
    {
        $this->getResource()->loadByFilterOption($this, $filter->getAttributeModel()->getAttributeCode(), $optionId);

        if (!$this->getId()) {
            $this->generateNewRewrite($filter, $optionId, Mage::app()->getStore()->getId());
        }

        return $this;
    }

    /**
     * In case we could not find a fitting dataset in database we generate a new one from given filter and option_id.
     * The current store's normalized and lowercased option label is used as speaking url segment for the current url.
     *
     * The process checks, whether the given option_id value belongs to the given filter's attribute model and whether
     * the attribute model is filterable (though this should work anyways as only filterable attributes can be used to
     * create attribute filters).
     *
     * @param Mage_Catalog_Model_Layer_Filter_Attribute $filter The given attribute Filter.
     * @param int $optionId The wanted option id.
     * @param int $storeId Id of the currently active store
     * @return Mirasvit_SeoFilter_Model_Rewrite|false On success Self, otherwise false.
     */
    public function generateNewRewrite($filter, $optionId, $storeId)
    {
        if (empty($filter) || !(int) $optionId) {
            return FALSE;
        }

        // load option from option_id
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setStoreFilter($storeId, true);

        // normally this should be done using the setIdFilter option of the collection. Unfortunately this results in an
        // error in Magento version 1.6.2.0
        $optionCollection->getSelect()->where('`main_table`.`option_id` = ?', $optionId);
        $option = $optionCollection->getFirstItem();

        // get all currently filterable attributes
        $category = Mage::registry('current_category');
        $filterableAttributes = Mage::getSingleton('seofilter/catalog_layer')->setCurrentCategory($category)->getFilterableAttributes()->getItems();

        // failure, if current attribute not filterable or the option does not belong to the given attribute model
        if (!in_array($option['attribute_id'], array_keys($filterableAttributes))
            || $filterableAttributes[$option['attribute_id']]->getAttributeCode() != $filter->getAttributeModel()->getAttributeCode()) {
            return FALSE;
        }

        // get normalized and lowercased version of the options label
        if (extension_loaded('mbstring')) {
            $label = mb_strtolower(Mage::helper('seofilter')->normalize($option->getValue()));
        } else {
            $label = strtolower(Mage::helper('seofilter')->normalize($option->getValue()));
        }

        if (empty($label)) {
            $label = $optionId;
        }

        // try to load the label, try to avoid duplication of rewrite strings by simple alternations
        $rewrite = Mage::getModel('seofilter/rewrite')->loadByRewriteString($label);
        $addition = 0;
        while ($rewrite->getId()) {
            $rewrite = Mage::getModel('seofilter/rewrite')->loadByRewriteString($label . '_' . ++$addition);
        }

        if ($addition) {
            $label .= '_' . $addition;
        }

        //set data, save to databaseich h and return new values
        $this->setAttributeCode($filter->getAttributeModel()->getAttributeCode())
            ->setOptionId($optionId)
            ->setRewrite($label)
            ->setStoreId($storeId)
            ->save();

        return $this;
    }

    /**
     * Loads current rewrite from given rewrite string.
     * This is triggered upon url routing - at this point in time we only have the rewrite string and need to translate
     * it into the original url parameters.
     *
     * @param string $rewriteString
     * @return Mirasvit_SeoFilter_Model_Rewrite Self.
     */
    public function loadByRewriteString($rewriteString)
    {
        $this->getResource()->loadByRewriteString($this, $rewriteString);

        return $this;
    }

}