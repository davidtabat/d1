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
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Conditions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {
    
    /**
     * Get margin criteria as html
     * 
     * @param string $id
     * @return string $html 
     */
    public function getMarginCriteria($id){
        
        $html = '';
        $value = Mage::getStoreConfig($this->getPath($id));
        $html .= '<select style="float:left;width:200px;" name="'.$this->getName($id).'" id="'.$this->getId($id).'">';
        $html .= '<option></option>';
        
        for($i = 0; $i < 100; $i++){
            
            $selected = ($i == $value) ? 'selected' : '';
            $html .= '<option value="'.$i.'" '.$selected.'>'.$i.' %</option>';
            
        }
        
        $html .= '</select>';
        
        return $html;
        
    }
    
    /**
     * Ge tattribute set criteria as html
     * 
     * @param string $id
     * @return string $html
     */
    public function getAttributeSetCriteria($id){
        
        $html = '';
        $values = Mage::getStoreConfig($this->getPath($id));
        $valuesTab = explode(',', $values);
        $html .= '<select style="float:left;width:200px;" multiple="multiple" name="'.$this->getName($id, true).'" id="'.$this->getId($id).'" size="10" class=" select">';
        
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();
        
        foreach($sets as $k => $set){
            
            $selected = (in_array($k, $valuesTab)) ? 'selected' : '';
            $html .= '<option value="'.$k.'" '.$selected.'>'.$set.'</option>';
            
        }
        
        $html .= '</select>';
        
        return $html;
        
    }
    
    /**
     * Get visibility criteria as html
     * 
     * @param string $id
     * @return string $html 
     */
    public function getVisibilityCriteria($id){
        
        $html = '';
        $values = Mage::getStoreConfig($this->getPath($id));
        $valuesTab = explode(',', $values);
        $html .= '<select style="float:left;width:200px;" multiple="multiple" name="'.$this->getName($id, true).'" id="'.$this->getId($id).'" size="10" class=" select">';
        
        $visibility = Mage::getModel('catalog/product_visibility')->getOptionArray();
        
        foreach($visibility as $k => $v){
            
            $selected = (in_array($k, $valuesTab)) ? 'selected' : '';
            $html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            
        }
        
        $html .= '</select>';
        
        return $html;        
        
    }
    
    /**
     * Get brand criteria as html
     * 
     * @param string $id
     * @return string $html 
     */
    public function getBrandCriteria($id){
        
        $html = '';
        $values = Mage::getStoreConfig($this->getPath($id));
        $valuesTab = explode(',', $values);
        $html .= '<select style="float:left;width:200px;" multiple="multiple" name="'.$this->getName($id, true).'" id="'.$this->getId($id).'" size="10" class=" select">';
        
        $product = Mage::getModel('catalog/product');
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($product->getResource()->getTypeId())
                ->addFieldToFilter('attribute_code', Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_manufacturer_attribute());
        $attribute = $attributes->getFirstItem()->setEntity($product->getResource());
        $manufacturers = $attribute->getSource()->getAllOptions(false);
        
        foreach($manufacturers as $manufacturer){
            $selected = (in_array($manufacturer['value'], $valuesTab)) ? 'selected' : '';
            $html .= '<option value="'.$manufacturer['value'].'" '.$selected.'>'.$manufacturer['label'].'</option>';
        }
        
        $html .= '</select>';
        
        return $html;
        
    }
    
    /**
     * Get operator comp as html
     * 
     * @param string $id
     * @return string $html 
     */
    public function getOperatorComp($id){
        
        $value = Mage::getStoreConfig($this->getPath($id));        
        
        $html = '';
        $html .= '<select style="float:left;width:80px;" name="'.$this->getName($id).'" id="'.$this->getId($id).'">';
        
        foreach(Mage::Helper('MarketPlace/AutoSubmit')->getOperatorsCompAsArray() as $k => $v){
            
            $selected = ($value == $k) ? 'selected' : '';
            
            $html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            
        }
        
        $html .= '</select>';
        
        return $html;
        
    }
    
    /**
     * Get operator global as html
     * 
     * @param string $id
     * @return string $html
     */
    public function getOperatorGlobal($id){
        
        $value = Mage::getStoreConfig($this->getPath($id));
        
         $html = '';
        $html .= '<select style="float:left;width:80px;" name="'.$this->getName($id).'" id="'.$this->getId($id).'">';
        
        foreach(Mage::Helper('MarketPlace/AutoSubmit')->getOperatorsGlobalAsArray() as $k => $v){
            
            $selected = ($k == $value) ? 'selected' : '';
            $html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
            
        }
        
        $html .= '</select>';                
        
        return $html;
    }
    
    /**
     * Get form fieldset name
     * 
     * @param string $id
     * @return string $retour 
     */
    public function getName($id, $multiple = false){
        $retour = 'groups[soumission_auto][fields]['.$id.'][value]';
        
        if($multiple === true)
            $retour .= '[]';
        
        return $retour;
    }      
    
    /**
     * Get Id
     * 
     * @param string $id
     * @return string 
     */
    public function getId($id){
        return 'marketplace_soumission_auto_'.$id;
    }
    
    /**
     * Get config path
     * 
     * @param string $id
     * @return string 
     */
    public function getPath($id, $op = false){
        
        $path = 'marketplace/soumission_auto/'.$id;
        
        if($op === true)
            $path .= '_operator';
        
        return $path;
    }
    
}
