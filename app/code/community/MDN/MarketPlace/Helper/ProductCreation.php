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
class MDN_MarketPlace_Helper_ProductCreation extends MDN_MarketPlace_Helper_Feed {

    const kStatusCreated = "created";
    const kStatusPending = "pending";
    const kStatusNotCreated = "notCreated";
    const kStatusInError = "error";
    const kStatusIncomplete = 'incomplete';
    const kStatusActionRequired = 'action_required';
    const kProductTitleTypeStandard = 'standard';
    const kProductTitleTypeCustom = 'custom';
    const kProductTitleStyleUppercase = 'uppercase';
    const kProductTitleStyleLowercase = 'lowercase';
    const kProductTitleStyleDefault = 'default';
    
    /**
     * Get Product title types
     * 
     * @return array 
     */
    public function getProductTitleTypes(){
        return array(
            self::kProductTitleTypeStandard => 'Standard',
            self::kProductTitleTypeCustom => 'Custom'
        );
    }
    
    /**
     * Get product title styles
     * 
     * @return array 
     */
    public function getProductTitleStyles(){
        return array(
            self::kProductTitleStyleDefault => 'Standard',
            self::kProductTitleStyleLowercase => 'Lower case',
            self::kProductTitleStyleUppercase => 'Upper case'
        );
    }
    

    /**
     * Get statuses as combo
     * 
     * @return array $retour 
     */
    public function getStatusesAsCombo(){
        
        $retour = array(
            self::kStatusCreated => Mage::helper('MarketPlace')->__('Created'),
            self::kStatusNotCreated => Mage::Helper('MarketPlace')->__('Not created'),
            self::kStatusPending => Mage::Helper('MarketPlace')->__('Pending'),
            self::kStatusInError => Mage::Helper('MarketPlace')->__('Error'),
            self::kStatusIncomplete => Mage::Helper('MarketPlace')->__('Incomplete'),
            self::kStatusActionRequired => Mage::Helper('MarketPlace')->__('Action Required')
        );                
        
        return $retour;
    }
    
    /**
     * Return status list
     *
     * @return array
     */
    public function getStatus(){

        $retour = array(
            self::kStatusCreated => self::kStatusCreated,
            self::kStatusNotCreated => self::kStatusNotCreated,
            self::kStatusPending => self::kStatusPending,
            self::kStatusInError =>self::kStatusInError,
            self::kStatusIncomplete => self::kStatusIncomplete,
            self::kStatusActionRequired => self::kStatusActionRequired
        );

        return $retour;

    }

    /**
     * Get export path name
     *
     * @return string
     *
     * @see getMarketPlaceName
     */
    public function getExportPath($marketplaceName){
        return Mage::app()->getConfig()->getTempVarDir().'/export/marketplace/'.$marketplaceName.'/';
    }

    /**
     * Getter status incomplete
     *
     * @return string
     */
    public function getStatusIncomplete(){
        return self::kStatusIncomplete;
    }

    /**
     * Getter status not created
     *
     * @return string
     */
    public function getStatusNotCreated(){
        return self::kStatusNotCreated;
    }

    /**
     * Getter status created
     *
     * @return string
     */
    public function getStatusCreated(){
        return self::kStatusCreated;
    }

    /**
     * Getter status pending
     *
     * @return string
     */
    public function getStatusPending(){
        return self::kStatusPending;
    }

    /**
     * Getter status in error
     *
     * @return string
     */
    public function getStatusInError(){
        return self::kStatusInError;
    }

    /**
     * Update product status
     *
     * @param request $request
     */
    public function updateStatus($request){

        $ids = $request->getPost('product_ids');
        $countryId = $request->getParam('country_id');
        $status = ($request->getParam('status') == $this->getStatusNotCreated()) ? new Zend_Db_Expr('null') : $request->getParam('status');
        Mage::getModel('MarketPlace/Data')->updateStatus($ids, $countryId, $status);

    }

    /**
     * Is marketplace allow product feed generation ?
     *
     * @return boolean
     */
    public function allowGenerateProductFeed(){
        return false;
    }

    /**
     * Allow matching EAN
     *
     * @return boolean
     */
    public function allowMatchingEan(){
        return false;
    }
    
    /**
     * Allow delete products
     * 
     * @return boolean 
     */
    public function allowDeleteProduct(){
        return false;
    }
    
    /**
     * Get product title
     * 
     * @param type $product
     * @return string $retour 
     */
    public function getProductTitle($product){
        
        $retour = '';
        
        $productTitleType = Mage::registry('mp_country')->getParam('product_title_type');
        $productTitleStyle = Mage::registry('mp_country')->getParam('product_title_style');
        
        switch($productTitleType){         
            
            case self::kProductTitleTypeCustom:
                $customString = Mage::registry('mp_country')->getParam('product_custom_title');
                
                preg_match_all('#{{(\w+)}}#', $customString, $match);
                
                foreach($match[1] as $attributeName){
 
                    if($product->getData($attributeName) && $product->getAttributeText($attributeName)){
                        
                        $customString = str_replace('{{'.$attributeName.'}}', $product->getAttributeText($attributeName), $customString);

                    }else{

                        if($product->getData($attributeName)){
                            
                            $customString = str_replace('{{'.$attributeName.'}}', $product->getData($attributeName), $customString);
                            
                        }else{
                            // no value, replace by empty string
                           $customString = str_replace('{{'.$attributeName.'}}', '', $customString);
                        }
                        
                    }
                    
                }

                $retour = $customString;
                
                break;
            
            case self::kProductTitleStyleDefault:
            default:
                $retour = $product->getname();
                break;
            
        }

        switch($productTitleStyle){
            
            case self::kProductTitleStyleLowercase:
                $retour = strtolower($retour);
                break;
            
            case self::kProductTitleStyleUppercase:
                $retour = strtoupper($retour);
                break;
            
            case self::kProductTitleStyleDefault:
            default :
                // nothing to do
                break;                            
            
        }
        
        return Mage::Helper('MarketPlace/Product')->formatExportedTxt($retour);
        
    }
    
    /**
     * Generate product feed
     * 
     * @param type $request
     * @return string
     */
    public function generateProductFeed($request){
        return Mage::Helper('MarketPlace')->__('Not available for this marketplace');
    }
    
    /**
     * Revise products
     * 
     * @param type $request
     * @throw Exception
     */
    public function massReviseProducts($request){
        throw new Exception(Mage::Helper('MarketPlace')->__('Not available for this marketplace'));
    }

}
