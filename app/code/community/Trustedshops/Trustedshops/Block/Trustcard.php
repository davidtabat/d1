<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Trustcard extends Trustedshops_Trustedshops_Block_Abstract
{
    /**
     * path to layout file
     *
     * @var string
     */
    protected $_template = 'trustedshops/trustcard.phtml';

    /**
     * magento order
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * remember gtin, mpn and brand attribute
     *
     * @var array
     */
    protected $_attributeCache = array();

    /**
     * get the current order from the checkout session
     * used on the checkout success page
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        try {
            if (is_null($this->_order)) {
                $session = Mage::getSingleton('checkout/session');
                $lastOrder = $session->getLastRealOrder();
                if (empty($lastOrder)) {
                    $lastOrder = $session->getLastOrderId();
                    $lastOrder = Mage::getModel('sales/order')->load($lastOrder);
                }
                $this->_order = $lastOrder;
            }
        } catch (Exception $e) {
            $this->_order = null;
        }

        return $this->_order;
    }

    /**
     * check if we should collect order data
     * always true for standard mode
     *
     * @return bool
     */
    public function collectOrders()
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->isExpert()) {
            return $this->getConfig('collect_orders', 'trustbadge');
        }
        return true;
    }

    /**
     * check if we should collect product data
     *
     * @return bool
     */
    public function collectReviews()
    {
        if (!$this->getOrder()) {
            return false;
        }

        if ($this->isExpert()) {
            if (!$this->getConfig('collect_orders', 'trustbadge')) {
                return false;
            }
            return $this->getConfig('expert_collect_reviews', 'product');
        }
        return $this->getConfig('collect_reviews', 'product');
    }

    /**
     * @return string
     */
    public function getIncrementId()
    {
        if (!$this->getOrder()) {
            return "";
        }

        return $this->getOrder()->getIncrementId();
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        if (!$this->getOrder()) {
            return "";
        }

        return $this->getOrder()->getCustomerEmail();
    }

    /**
     * get the formatted order amount
     *
     * @return string
     */
    public function getOrderAmount()
    {
        if (!$this->getOrder()) {
            return "";
        }

        return $this->getOrder()->getGrandTotal();
    }

    /**
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        if (!$this->getOrder()) {
            return "";
        }

        return $this->getOrder()->getStoreCurrencyCode();
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        if (!$this->getOrder()) {
            return "";
        }

        if (!$this->getOrder()->getPayment()) {
            return "";
        }

        return $this->getOrder()->getPayment()->getMethod();
    }

    /**
     * get the estimated delivery date
     *
     * @return string
     */
    public function getEstimatedDeliveryDate()
    {
        $deliveryData = new Varien_Object();
        $deliveryData->setDate('');
        Mage::dispatchEvent('trustedshops_get_estimated_delivery_date', array(
            'order' => $this->getOrder(),
            'delivery_date' => $deliveryData
        ));
        return $deliveryData->getDate();
    }

    /**
     * get the product url
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return string
     */
    public function getProductUrl($item)
    {
        $product = $this->getProduct($item);

        return $product->getProductUrl();
    }

    /**
     * get the product image url
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return string
     */
    public function getProductImage($item)
    {
        $product = $this->getProduct($item);

        return (string)Mage::helper('catalog/image')->init($product, 'image');
    }

    /**
     * get the product name
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return string
     */
    public function getName($item)
    {
        return $item->getName();
    }

    /**
     * get the product sku
     * for composite products get the parents sku
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return string
     */
    public function getProductSkuByItem($item)
    {
        if ($item->getHasChildren()) {
            $product = $this->getProduct($item);
            return $product->getSku();
        }

        return $item->getSku();
    }

    /**
     * get the product gtin
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return string
     */
    public function getProductGTIN($item)
    {
        return $this->_getAttribute($item, 'gtin');
    }

    /**
     * get the product mpn
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return string
     */
    public function getProductMPN($item)
    {
        return $this->_getAttribute($item, 'mpn');
    }

    /**
     * get the product brand
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return string
     */
    public function getProductBrand($item)
    {
        return $this->_getAttribute($item, 'brand');
    }

    /**
     * fetch the product attribute depended of the attribute type
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param string $field
     * @return string
     */
    protected function _getAttribute($item, $field)
    {
        // handle different attributes for expert mode
        $prefix = "";
        if ($this->isExpert()) {
            $prefix = 'expert_';
        }

        $attributeCode = $this->getConfig($prefix . 'review_attribute_' . $field, 'product');
        if (empty($attributeCode)) {
            return '';
        }

        /*
         * load attributes of the child product
         */
        $product = $this->getProduct($item);
        if ($product->isComposite()) {
            $childItems = $item->getChildrenItems();
            $childItem = array_shift($childItems);
            if (!$childItem || !$childItem->getId()) {
                return "";
            }
            $product = $childItem->getProduct();
        }

        if (!$product || !$product->getId()) {
            return "";
        }

        $attribute = $this->getMagentoAttribute($attributeCode);

        switch ($attribute->getFrontendInput()) {
            case 'select':
                $value = $product->getAttributeText($attributeCode);
                break;
            case 'multiselect':
                $value = $product->getAttributeText($attributeCode);
                $value = implode(',', $value);
                break;
            default:
                $value = $product->getResource()
                    ->getAttributeRawValue($product->getId(), $attributeCode, Mage::app()->getStore());
                break;
        }

        return Mage::helper('core')->escapeHtml($value);
    }

    /**
     * @param $item
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($item)
    {
        $product = $item->getProduct();
        if (empty($product)) {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }

        return $product;
    }

    /**
     * load the magento base attribute
     *
     * @param string $attributeCode
     * @return Mage_Eav_Model_Entity_Attribute
     */
    protected function getMagentoAttribute($attributeCode)
    {
        if (empty($this->_attributeCache[$attributeCode])) {
            $this->_attributeCache[$attributeCode] = Mage::getModel('eav/entity_attribute')
                ->loadByCode('catalog_product', $attributeCode);
        }

        return $this->_attributeCache[$attributeCode];
    }
}
