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
class MDN_MarketPlace_Block_Configuration_Tree extends Mage_Adminhtml_Block_Template {
    
    /**
     * Prepare layout
     * 
     * @return type 
     */
    protected function _prepareLayout(){
        
        $this->setChild('add_account_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                    'label' => $this->__('Add New'),
                    'onclick' => 'editSet.listMp();',
                    'class' => 'add'
                ))
        );        
        
        return parent::_prepareLayout();
        
    }
    
    /**
     * get new button
     * 
     * @return type 
     */
    public function getNewButton(){
        return $this->getChildHtml('add_account_button');
    }
    
    /**
     * Get account tree as json
     * 
     * @return string 
     */
    public function getAccountTreeJson(){
        
        $items = array();
        $nbr = 0;
        
        foreach(Mage::Helper('MarketPlace')->getHelpers() as $k => $v){
            
            $helper = Mage::Helper($v);
            $name = $helper->getMarketPlaceName();
            
            $collection = Mage::getModel('MarketPlace/Accounts')->getCollection()
                                ->addFieldToFilter('mpa_mp', $name);
            
            $nbr = count($collection);
                       
            $mp['text'] = $name. ' ('.$nbr.')';
            $mp['id'] = 'mp_'.$name;
            $mp['cls'] = 'folder active-category';
            $mp['allowDrop'] = false;
            $mp['allowDrag'] = false;
            $mp['children'] = array();
                    
            foreach($collection as $item){                
                
                $params = unserialize($item->getmpa_params());
                $activeClass = (array_key_exists('is_active', $params) && $params['is_active'] == 1) ? 'active-account' : 'no-active-account';
                
                $account = array();
                $account['text'] = $item->getmpa_name();
                $account['id'] = 'account_'.$item->getmpa_id();
                $account['cls'] = 'folder active-category '.$activeClass;
                $account['allowDrop'] = false;
                $account['allowDrag'] = false;
                $account['children'] = array();
                $account['leaf'] = false;            
                
                $country = array();                                                
                $collection = Mage::getModel('MarketPlace/Countries')->getCollection()->addFieldToFilter('mpac_account_id', $item->getmpa_id());
                
                foreach($collection as $countryObj){                    
                    
                    $mpac_params = unserialize($countryObj->getmpac_params());
                    
                    $activeClass = (array_key_exists('active', $mpac_params) && $mpac_params['active'] == 1) ? 'active-country' : 'no-active-country';
                    
                    $country = array(
                        'text' => $mpac_params['name'],
                        'id' => 'country_'.$countryObj->getId(),
                        'cls' => 'folder active-category '.$activeClass,
                        'allowDrop' => false,
                        'allowDrag' => false,
                        'leaf' => true,
                        'children' => array()
                    );
                    
                    $account['children'][] = $country;
                    
                }
                
                $mp['children'][] = $account;
               
            }
            
            $items[] = $mp;
            
        }      
        
        $retour = Mage::Helper('core')->jsonEncode($items);
        
        return $retour;        
        
    }
    
}
