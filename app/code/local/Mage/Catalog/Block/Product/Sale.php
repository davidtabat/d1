<?php

class Mage_Catalog_Block_Product_Sale extends Mage_Catalog_Block_Product_List
{
    
    //public $_collection;  
    
    public function getProductsLimit() 
    { 
        if ($this->getData('limit')) {
            return intval($this->getData('limit'));
        } else {
            return 32;
        }
    }
	
	public function __construct()
    {
        parent::__construct();
        $collection = $this->_getProductCollection();
        $this->setCollection($collection);
    }

	
	
	
	

     protected function _getProductCollection()  
     {  
                $page = Mage::getBlockSingleton('page/html_pager')->getCurrentPage();
				date_default_timezone_set(Mage::getStoreConfig('general/locale/timezone'));
				$todayDate = strftime("%Y-%m-%d",Mage::app()->getLocale()->storeTimeStamp(Mage::app()->getStore()->getId()));
				$storeId    = Mage::app()->getStore()->getId();  
                $product    = Mage::getModel('catalog/product');  
                              
                $this->_productCollection = $product->setStoreId($storeId)  
                    ->getCollection()  
                    ->addAttributeToSelect(array('delivery_time', 'werbetext', 'kompatibel', 'farbe', 'tonerkapazitaet', 'festplatte', 'speicher', 'druck_kopiergeschwindigkeit', 'papierformat_max', 'funktionen', 'mpn', 'art', 'name','status', 'price', 'special_price', 'small_image','required_options','special_from_date', 'special_to_date'), 'inner')
					->joinField('stock_status','cataloginventory/stock_status','stock_status',
						'product_id=entity_id', array(
						  'stock_status' => Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK,
						  'website_id' => Mage::app()->getWebsite()->getWebsiteId(),
						))
					->addAttributeToFilter('special_price', array('gt' => 0), 'left')
					->addAttributeToFilter('special_from_date', array('date' => true, 'to' => $todayDate))
					->addAttributeToFilter('special_to_date', array('or'=> array(
						0 => array('date' => true, 'from' => $todayDate),
						1 => array('is' => new Zend_Db_Expr('null')))
					), 'left')
					//->setOrder('created_at', 'desc')
					->addAttributeToSort('created_at', 'desc')
					->addFinalPrice()
					->addStoreFilter()
                    ->setPageSize($this->getProductsLimit())
					->setCurPage($page)
					->addAttributeToFilter('status', 1)
					->addUrlRewrite();
					
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);	
			Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->_productCollection);
            
                               
                $checkedProducts = new Varien_Data_Collection();
                    foreach ($this->_productCollection as $k => $p) {
                        $p = $p->loadParentProductIds();
                        $parentIds = $p->getData('parent_product_ids');

                        if (is_array($parentIds) && !empty($parentIds)) {
                            if (!$checkedProducts->getItemById($parentIds[0])) {
                                $parentProduct = Mage::getModel('catalog/product')->setStoreId($storeId)->load($parentIds[0]);
                                if ($parentProduct->isVisibleInCatalog()) {
                                    $checkedProducts->addItem($parentProduct);
                                }
                            }
                        } else {
                            if (!$checkedProducts->getItemById($k)) {
                                $checkedProducts->addItem($p);
                            }
                        }
                        if (count($checkedProducts) >= $this->getProductsLimit()) {
                            break;
                        }
                    }
  return $this->_productCollection;  
    
     }  
} 