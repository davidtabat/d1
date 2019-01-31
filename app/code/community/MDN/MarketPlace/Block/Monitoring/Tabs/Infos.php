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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Block_Monitoring_Tabs_Infos extends Mage_Adminhtml_Block_Template {
 
    protected $_currentCountry = null;
    
    /**
     * Construct 
     */
    public function __construct(){
        
        parent::__construct();
        $this->setTemplate('MarketPlace/Monitoring/Tabs/Infos.phtml');
        
    }
    
    /**
     * get countries as combo
     * 
     * @return string 
     */
    public function getCountriesAsCombo(){
        $html = '';        
        
        $accounts = Mage::getModel('MarketPlace/Accounts')->getActiveCountries($this->getMp());
                
        $html .= '<select name="" id="" onchange="switchCountry(this.value);">';

        foreach($accounts as $account){

            $html .= '<optgroup label="'.$account['label'].'">';

            foreach($account['countries'] as $k => $v){

                $selected = (Mage::registry('mp_country')->getId() == $k) ? 'selected' : '';

                $html .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';

            }

            $html .= '</optgroup>';

        }        

        $html .= '</select>';   
        
        return $html;
    }
    
    /**
     * Get informations
     * 
     * @return array $retour 
     */
    public function getInformations(){
        
        $countryId = Mage::registry('mp_country')->getId();
        $statuses = Mage::Helper('MarketPlace/ProductCreation')->getStatusesAsCombo();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = '';
        $retour = array();
        
        $sql = 'SELECT COUNT(*) AS nbr, mp_marketplace_status AS status
                FROM '.$prefix.'market_place_data
                WHERE mp_marketplace_id = '.$countryId.'    
                GROUP BY mp_marketplace_status';
        
        $res = $read->fetchAll($sql);                
        
        // init
        foreach($statuses as $status => $label){            
            $retour[$status] = array(
                                'nbr' => 0,
                                'label' => $label,
                                'messages' => array()
                               );            
        }
        
        // populate
        foreach($res as $elt){
            $retour[$elt['status']]['nbr'] = $elt['nbr'];
            
            /*if(in_array($elt['status'], array("error", "action_required"))){
                
                $message = '';
                
                // add messages
                $sql = 'SELECT mp_message, mp_product_id
                        FROM market_place_data
                        WHERE mp_marketplace_id = '.$countryId.'
                        AND mp_marketplace_status = "'.$elt['status'].'"';
                
                $res = $read->fetchAll($sql);
                
                foreach($res as $item){
                    
                    $message .= $item['mp_product_id'].' '.$item['mp_message'];
                    
                    if($elt['status'] == 'error'){
                        
                        $addUrl = $this->getUrl('MarketPlace/Products/processFromMonitoring', array('action' => 'add_'.$item['mp_product_id'].'_'.$countryId, 'productId' => $item['productId'], 'countryId' => $countryId));
                        $matchUrl = $this->getUrl('MarketPlace/Products/processFromMonitoring', array('action' => 'match_'.$item['mp_product_id'].'_'.$countryId, 'productId' => $item['productId'], 'countryId' => $countryId));
                        
                        $message .= ' | <a href="'.$addUrl.'">'.$this->__('Add').'</a> | <a href="'.$matchUrl.'">'.$this->__('Match').'</a>';
                        
                    }
                    
                    $retour[$elt['status']]['messages'][] = $message;
                    
                }
                
            }*/
            
        }                             
        
        return $retour;
        
    }
    
    /**
     * Get action link
     * 
     * @param string $status
     * @return string $html
     */
    public function getActionLink($status){
        
        $html = '';
        
        switch($status){
            
            case MDN_MarketPlace_Helper_ProductCreation::kStatusNotCreated:
                // matching ean link
                $html .= '<a href="'.$this->getUrl('MarketPlace/Products/autoSubmit', array('countryId' => Mage::registry('mp_country')->getId())).'">'.$this->__('Auto submit').'</a>';
                break;
            default:
                // download list + message
                $html .= '<a href="'.$this->getUrl('MarketPlace/Monitoring/download', array('status' => $status, 'countryId' => Mage::registry('mp_country')->getId())).'">'.$this->__('Download').'</a>';
                break;
            
        }
        
        return $html;
        
    }
    
    /**
     * Get title
     * 
     * @return string $html
     */
    public function getTitle(){
        
        $retour = '';
        
        $account = Mage::getModel('MarketPlace/Accounts')->load(Mage::registry('mp_country')->getmpac_account_id());
        
        $retour .= $account->getmpa_name().' - '.Mage::registry('mp_country')->getParam('name');
        
        return $retour;
        
    }
    
}
