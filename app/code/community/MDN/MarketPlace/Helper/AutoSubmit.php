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
class MDN_MarketPlace_Helper_AutoSubmit extends Mage_Core_Helper_Abstract {
    
    const kMargin = 'so_marginfilter';
    const kAttributeSet = 'so_attrset';
    const kVisibility = 'so_visibility';
    const kBrand = 'so_brand';
    const kCustomAttribute = 'so_attribute';
    
    /**
     * Get custom attributes
     * 
     * @return array 
     */
    public function getCustomAttributes(){
        
        return array(
            'one',
            'two',
            'three'
        );
        
    }
    
    /**
     * get products request
     * 
     * @return array $res 
     */
    public function getProducts(){
        
        $retour = array();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $res = $read->fetchAll($this->getSql());
        
        foreach($res as $k => $v)
            $retour[] = $v['entity_id'];
        
        return $retour;
        
    }
    
    /**
     * Ge tsql query
     *  
     * @return string $sql
     */
    public function getSql(){
        
        $country = Mage::registry('mp_country');
        
        $entityTypeId = Mage::getModel('eav/config')->getEntityType('catalog_product')->getId();
        
        $customAttributeLabel = self::kCustomAttribute;
        $tCustomOperators = array();
        
        $brandOperator = ($country->getParam(self::kBrand.'operator') == 'nin' ) ? 'NOT IN' : $country->getParam(self::kBrand.'operator');
        $visibilityOperator = ($country->getParam(self::kVisibility.'operator') == 'nin' ) ? 'NOT IN' : $country->getParam(self::kVisibility.'operator');
        //$marginOperator = $country->getParam(self::kMargin.'operator');
        $attributeSetOperator = ($country->getParam(self::kAttributeSet.'operator') == 'nin') ? 'NOT IN' : $country->getParam(self::kAttributeSet.'operator');        
        
        $prefix = Mage::getConfig()->getTablePrefix();    

        $sql = 'SELECT p.entity_id
                FROM '.$prefix.'catalog_product_entity AS p
                LEFT JOIN '.$prefix.'market_place_data AS m ON (m.mp_product_id = p.entity_id) AND (mp_marketplace_id = '.$country->getId().')';
        
        if($brandOperator != ''){
            // add brand filter
            $brandAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', Mage::getModel('MarketPlace/Configuration')->getGeneralConfigObject()->getmp_brand_attribute())->getId();                
            $sql .= ' LEFT JOIN '.$prefix.'catalog_product_entity_int AS b ON (b.entity_id = p.entity_id) AND (b.entity_type_id = '.$entityTypeId.') AND (b.attribute_id = '.$brandAttrId.')';
        }        
        
        if($visibilityOperator != ''){
            // add visibility filter
            $visibilityAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'visibility')->getId();
            $sql .= ' LEFT JOIN '.$prefix.'catalog_product_entity_int AS v ON (v.entity_id = p.entity_id) AND (v.entity_type_id = '.$entityTypeId.') AND (v.attribute_id = '.$visibilityAttrId.')';
        }
               
        // add conditions
        /*if($marginOperator != ''){
            // add margin filter
            $priceAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'price')->getId();
            $costAttrId = Mage::getModel('eav/config')->getAttribute('catalog_product', 'cost')->getId();
            
            $sql .= ' LEFT JOIN '.$prefix.'catalog_product_entity_decimal AS pr ON (pr.entity_id = p.entity_id) AND (pr.entity_type_id = '.$entityTypeId.') AND (pr.attribute_id = '.$priceAttrId.')';
            $sql .= ' LEFT JOIN '.$prefix.'catalog_product_entity_decimal AS co ON (co.entity_id = p.entity_id) AND (co.entity_type_id = '.$entityTypeId.') AND (co.attribute_id = '.$costAttrId.')';
        } */ 
                
        // add custom operators filter
        foreach($this->getCustomAttributes() as $customAttributeName){
                        
            $customOperator = $country->getParam($customAttributeLabel.$customAttributeName.'operator');
            if($customOperator != ''){
                
                $customOperator = ($customOperator == 'nin') ? 'NOT IN' : $customOperator;
                $tCustomOperators[$customAttributeName] = $customOperator;
                $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $country->getParam($customAttributeLabel.$customAttributeName));
                $customAttrId = $attribute->getId();
                $sql .= ' LEFT JOIN '.$prefix.'catalog_product_entity_'.$attribute->getbackend_type().' AS ca'.$customAttributeName.' ON (ca'.$customAttributeName.'.entity_id = p.entity_id) AND (ca'.$customAttributeName.'.entity_type_id = '.$entityTypeId.') AND (ca'.$customAttributeName.'.attribute_id = '.$customAttrId.')';
                
            }            
            
        }
        
        // add where close
        $sql .= ' WHERE (m.mp_marketplace_status = "" OR m.mp_marketplace_status IS NULL OR m.mp_marketplace_status = "notCreated")';        
        
        // add attribute set filter
        if($attributeSetOperator != '')            
            $sql .= ' AND p.attribute_set_id '.strtoupper($attributeSetOperator).' ('.implode(',',$country->getParam(self::kAttributeSet)).')';
        
        // add brand filter
        if($brandOperator != '')            
            $sql .= ' AND b.value '.strtoupper($brandOperator).' ('.implode(',',$country->getParam(self::kBrand)).')';        
        
        // ad visibility filter
        if($visibilityOperator != '')            
            $sql .= ' AND v.value '.strtoupper($visibilityOperator).' ('.implode(',',$country->getParam(self::kVisibility)).')';        
        
        // add margin filter
        /*if($marginOperator != ''){
            
            $op = ($marginOperator == 'gt') ? '>' : '<';
            $sql .= ' AND ((((pr.value - co.value) / pr.value) * 100) '.$op.' '.$country->getParam(self::kMargin).')';
            
        }*/
        
        // add custom operators filter
        foreach($tCustomOperators as $name => $operator){
                
            $values = $country->getParam($customAttributeLabel.$name.'values');
            $tmp = explode(',', $values);
            foreach($tmp as $k => $item){
                $tmp[$k] = '"'.$item.'"';
            }           
            $sql .= ' AND ca'.$name.'.value '.strtoupper($operator).' ('.implode(',',$tmp).')';
            
        }
        
        // limit
        $sql .= ' LIMIT 0,100';
        
        echo $sql;die();
        
        return $sql;
        
    }
    
    /**
     * Get operators comp as array
     * 
     * @return array 
     */
    public function getOperatorsCompAsArray(){
        return array(
            '' => '',
            'gt' => Mage::Helper('MarketPlace')->__('Gretter than'),
            'lt' => Mage::Helper('MarketPlace')->__('Lower than')
        );
    }
    
    /**
     * Get operators global as array 
     * 
     * @return array
     */
    public function getOperatorsGlobalAsArray(){
        return array(
            '' => '',
            'in' => Mage::Helper('MarketPlace')->__('In'),
            'nin' => Mage::Helper('MarketPlace')->__('Not in')    
        );
    }
    
}
