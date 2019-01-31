<?php
/* 
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright  Copyright (c) 2013 Boost My Shop (http://www.boostmyshop.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Widget_Grid_Column_Filter_Barcode extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text {    

    /**
     * Get condition
     * 
     * @return array $retour 
     */
    public function getCondition()
    {
        
        $retour = array();                
        
        if(Mage::Helper('MarketPlace')->isErpInstalled()){
            
            $searchString = $this->getValue();

            $prefix = Mage::getConfig()->getTablePrefix();
            $sql = 'SELECT * FROM '.$prefix.'purchase_product_barcodes
                    WHERE ppb_barcode LIKE "%'.$searchString.'%"';
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $res = $read->fetchAll($sql);
        
            $productIds = array();
            foreach($res as $item){
                
                // o nse base sur le code barre et non le product id
                $productIds[] = $item['ppb_barcode'];
                
            }
            
            /*$barcodes = mage::getModel('AdvancedStock/ProductBarcode')
                                    ->getCollection()
                                    ->addFieldToFilter('ppb_barcode', array('like' => '%'.$searchString.'%'));
            $productIds = array();
            foreach ($barcodes as $barcode)
            {
                    $productIds[] = $barcode->getppb_product_id();
            }*/

            $retour =  array('in' => $productIds);
            
        }else{
            
            $retour = parent::getCondition();
            
        }

        return $retour;
    
    }
    
}
