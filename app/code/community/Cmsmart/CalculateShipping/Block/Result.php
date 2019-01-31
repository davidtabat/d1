<?php
class Cmsmart_CalculateShipping_Block_Result extends Mage_Core_Block_Template{

    public function getEstimate(){
        $estimate = Mage::getSingleton('calculateshipping/estimate');
        return $estimate;
    }

    /**
     * Retrieves result from estimate model
     *
     * @return array|null
     */
    public function getResult()
    {
        return $this->getEstimate()->getResult();
    }


    /**
     * Check result exist
     *
     * @return boolean
     */
    public function hasResult()
    {
        $flag = false;

        if($this->getResult())
            $flag = true;

        return $flag;
    }


    /**
     * Retrieve carrier name for shipping rate groups
     *
     * @param string $code
     * @return string|null
     */
    public function getCarrierName($code)
    {
        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code);

        if ($carrier) {
            return $carrier->getConfigData('title');
        }

        return null;
    }


    /**
     * Retrieve shipping price for current address and rate
     *
     * @param $price
     * @param boolean $flag show include tax price flag
     * @return string
     */
    public function getShippingPrice($price, $flag)
    {
        return $this->formatPrice(
            $this->helper('tax')->getShippingPrice(
                $price,
                $flag,
                $this->getEstimate()->getQuote()->getShippingAddress()
            )
        );
    }

    /**
     * Format price value depends on store settings
     *
     * @param $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getEstimate()->getQuote()->getStore()->convertPrice($price, true);
    }


    /**
     * Retrieve product for the estimation
     */
    public function getProduct()
    {
        return $this->getEstimate()->getProduct();
    }


    /**
     * Check if customer use include cart
     *
     * @return boolean
     */

    public function isIncludeCart(){

       return $this->getEstimate()->isIncludeCart();

    }


    /**
     * Get array of last added items
     *
     * @var $count
     * @return array
     */
    public function getRecentItems($count = null)
    {
        if ($count === null) {
            $count = $this->getItemCount();
        }

        $items = array();
//        if (!$this->getSummaryCount()) {
//            return $items;
//        }

        $i = 0;

        $allItems = array_reverse($this->getItems());
        foreach ($allItems as $item) {
            /* @var $item Mage_Sales_Model_Quote_Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $productId = $item->getProduct()->getId();
                $products  = Mage::getResourceSingleton('catalog/url')
                    ->getRewriteByProductStore(array($productId => $item->getStoreId()));
                if (!isset($products[$productId])) {
                    continue;
                }
                $urlDataObject = new Varien_Object($products[$productId]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }

            $items[] = $item;
            if (++$i == $count) {
                break;
            }
        }

//        var_dump($items);die('thanh');

        return $items;
    }


    /**
     * Retrieve count of display recently added items
     *
     * @return int
     */
    public function getItemCount()
    {
        $count = $this->getData('item_count');
        if (is_null($count)) {
            $this->setData('item_count', $count);
        }
        return $count;
    }


    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int | float
     */
//    public function getSummaryCount()
//    {
//        if ($this->getData('summary_qty')) {
//            return $this->getData('summary_qty');
//        }
//        return Mage::getSingleton('checkout/cart')->getSummaryQty();
//    }


    /**
     * Get all cart items
     *
     * @return array
     */
    public function getItems()
    {
        $quote = $this->getEstimate()->getQuote();
        return $quote->getAllVisibleItems();
    }


    /**
     * Get item row html
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  string
     */
    public function getItemHtml(Mage_Sales_Model_Quote_Item $item)
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }


    protected $_itemRenders = array();


    public function __construct()
    {
        parent::__construct();
        $this->addItemRender('default', 'checkout/cart_item_renderer', 'cmsmart/calculateshipping/item/default.phtml');
    }


    /**
     * Add renderer for item product type
     *
     * @param   string $productType
     * @param   string $blockType
     * @param   string $template
     * @return  Mage_Checkout_Block_Cart_Abstract
     */
    public function addItemRender($productType, $blockType, $template)
    {
        $this->_itemRenders[$productType] = array(
            'block' => $blockType,
            'template' => $template,
            'blockInstance' => null
        );
        return $this;
    }

    /**
     * Get renderer block instance by product type code
     *
     * @param   string $type
     * @return  array
     */
    public function getItemRenderer($type)
    {
        if (!isset($this->_itemRenders[$type])) {
            $type = 'default';
        }
        if (is_null($this->_itemRenders[$type]['blockInstance'])) {
            $this->_itemRenders[$type]['blockInstance'] = $this->getLayout()
                ->createBlock($this->_itemRenders[$type]['block'])
                ->setTemplate($this->_itemRenders[$type]['template'])
                ->setRenderedBlock($this);
        }

        return $this->_itemRenders[$type]['blockInstance'];
    }


}
