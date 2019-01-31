<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Widget_Dependence extends Mage_Adminhtml_Block_Widget_Form_Element_Dependence
{
    /**
     * overwrite for fieldset depends
     *
     * @return string
     */
    protected function _getDependsJson()
    {
        if ($this->_isTrustedshopsPage()
            && version_compare(Mage::getVersion(), '1.7.0.0', '<')
        ) {
            return $this->_getTrustedShopsJsonDepends();

        }

        return parent::_getDependsJson();
    }

    /**
     * check if we are on the system config page of the trustedshops module
     *
     * @return bool
     */
    protected function _isTrustedshopsPage()
    {
        $pathInfo = Mage::app()->getRequest()->getOriginalPathInfo();
        return (strpos($pathInfo, 'edit/section/trustedshops') !== false);
    }

    /**
     * get the json depends config for magento < 1.7
     *
     * @return string
     */
    protected function _getTrustedShopsJsonDepends()
    {
        return <<<JSON
{
    "trustedshops_info_mode_notice": {
        "trustedshops_info_mode": "expert"
    },
    "trustedshops_trustbadge_variant": {
        "trustedshops_info_mode": "standard"
    },
    "trustedshops_trustbadge_offset": {
        "trustedshops_info_mode": "standard"
    },
    "trustedshops_trustbadge_code": {
        "trustedshops_info_mode": "expert"
    },
    "trustedshops_trustbadge_collect_orders": {
        "trustedshops_info_mode": "expert"
    },
    "trustedshops_product_collect_reviews": {
        "trustedshops_info_mode": "standard"
    },
    "trustedshops_product_collect_reviews_notice": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_collect_orders_notice": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "0"
    },
    "trustedshops_product_expert_collect_reviews": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1"
    },
    "trustedshops_product_expert_collect_reviews_notice": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_expert_review_attribute_header": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_review_attribute_header": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_review_attribute_notice": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_review_attribute_notice": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_review_attribute_gtin": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_review_attribute_gtin": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_review_attribute_brand": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_review_attribute_brand": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_review_attribute_mpn": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_review_attribute_mpn": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_review_header": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_review_header": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_review_active": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_review_active": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_review_border_color": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1",
        "trustedshops_product_review_active": "1"
    },
    "trustedshops_product_review_star_color": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1",
        "trustedshops_product_review_active": "1"
    },
    "trustedshops_product_review_code": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1",
        "trustedshops_product_expert_review_active": "1"
    },
    "trustedshops_product_rating_header": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_rating_header": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_rating_active": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1"
    },
    "trustedshops_product_expert_rating_active": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1"
    },
    "trustedshops_product_rating_star_color": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1",
        "trustedshops_product_rating_active": "1"
    },
    "trustedshops_product_rating_star_size": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1",
        "trustedshops_product_rating_active": "1"
    },
    "trustedshops_product_rating_font_size": {
        "trustedshops_info_mode": "standard",
        "trustedshops_product_collect_reviews": "1",
        "trustedshops_product_rating_active": "1"
    },
    "trustedshops_product_rating_code": {
        "trustedshops_info_mode": "expert",
        "trustedshops_trustbadge_collect_orders": "1",
        "trustedshops_product_expert_collect_reviews": "1",
        "trustedshops_product_expert_rating_active": "1"
    }
}
JSON;
    }
}