<?php

/**
 * Product:       Xtento_OrderExport (1.8.5)
 * ID:            gynD3Fd2Yg7FQAAmb/3dfnkXJHnIofKqiiFav0FPkb8=
 * Packaged:      2015-09-09T07:08:21+00:00
 * Last Modified: 2014-12-07T22:35:48+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Condition/Combine.php
 * Copyright:     Copyright (c) 2015 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('xtento_orderexport/export_condition_combine');
    }

    public function getNewChildSelectOptions()
    {
        $attributes = array();
        $customAttributes = Mage::getSingleton('xtento_orderexport/export_condition_custom')->getCustomAttributes();
        foreach ($customAttributes as $code => $label) {
            if (preg_match('/xt\_billing\_/', $code)) {
                $attributes[] = array('value' => 'xtento_orderexport/export_condition_address_billing|' . str_replace('xt_billing_', '', $code), 'label' => $label);
            } else if (preg_match('/xt\_shipping\_/', $code)) {
                $attributes[] = array('value' => 'xtento_orderexport/export_condition_address_shipping|' . str_replace('xt_shipping_', '', $code), 'label' => $label);
            } else {
                $attributes[] = array('value' => 'xtento_orderexport/export_condition_object|' . $code, 'label' => $label);
            }
        }

        $otherAttributes = array();
        $customOtherAttributes = Mage::getSingleton('xtento_orderexport/export_condition_custom')->getCustomNotMappedAttributes();
        foreach ($customOtherAttributes as $code => $label) {
            $otherAttributes[] = array('value' => 'xtento_orderexport/export_condition_object|' . $code, 'label' => $label);
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value' => 'xtento_orderexport/export_condition_product_found', 'label' => Mage::helper('salesrule')->__('Product / Item attribute combination')),
            array('value' => 'xtento_orderexport/export_condition_product_subselect', 'label' => Mage::helper('salesrule')->__('Products subselection')),
            array('value' => 'xtento_orderexport/export_condition_combine', 'label' => Mage::helper('salesrule')->__('Conditions combination')),
            array('label' => Mage::helper('xtento_orderexport')->__('%s Attributes', ucfirst(Mage::registry('order_export_profile')->getEntity())), 'value' => $attributes),
            array('label' => Mage::helper('xtento_orderexport')->__('Misc. %s Attributes', ucfirst(Mage::registry('order_export_profile')->getEntity())), 'value' => $otherAttributes),
        ));

        $additional = new Varien_Object();
        Mage::dispatchEvent('xtento_orderexport_rule_condition_combine', array('additional' => $additional));
        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}
