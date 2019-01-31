<?php

/**
 * Cdiscount Package url management
 * 
 * @author Nicolas Mugnier <nicolas@boostmyshop.com>
 * @version 
 * @package MDN_Cdiscount
 */
class MDN_Cdiscount_Helper_Url extends Mage_Core_Helper_Abstract {
    
    /**
     * Get offer package URL
     * 
     * @param string $id
     * @return string $url
     */
    public function getOfferPackageUrl($id){
        $url = '';
        
        $url .= Mage::getUrl('Cdiscount/Package/download', array('type' => 'offers', 'filename' => $id));
        $url .= $id.'.zip';
        
        return $url;
    }
    
}