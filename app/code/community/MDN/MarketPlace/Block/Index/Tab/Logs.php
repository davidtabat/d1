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

class MDN_MarketPlace_Block_Index_Tab_Logs extends Mage_Adminhtml_Block_Widget_Grid{

    /**
     * Construct 
     */
    public function __construct(){

        parent::__construct();
        $this->setId('logs_grid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('Aucun elt');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

    }

    /**
     * Load collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {              
        
        $countryId = Mage::registry('mp_country')->getId();
        
        //charge
        $collection = mage::getModel('MarketPlace/Logs')
                                ->getCollection()
                                ->addFieldToFilter('mp_country', $countryId)
                                ->addAttributeToSort('mp_id', 'desc');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

     /**
     * Grid configuration
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        /*$this->addColumn('mp_id', array(
                'header'=> Mage::helper('sales')->__('Id'),
                'index' => 'mp_id'
        ));*/

        $this->addColumn('mp_date', array(
                'header'=> Mage::helper('sales')->__('Date'),
                'index' => 'mp_date',
                'type' => 'datetime'
        ));

        $this->addColumn('mp_message', array(
                'header'=> Mage::helper('sales')->__('Message'),
                'index' => 'mp_message',
                'width' => '800px',
                'renderer' => 'MDN_MarketPlace_Block_Widget_Grid_Column_Renderer_Log_Message'
        ));

        $this->addColumn('mp_execution_time', array(
                'header'=> Mage::helper('MarketPlace')->__('Execution time (micro sec)'),
                'index' => 'mp_execution_time',
                'type' => 'range' 
        ));
        
        $this->addColumn('mp_scope', array(
                'header' => Mage::Helper('MarketPlace')->__('Scope'),
                'index' => 'mp_scope',
                'type' => 'options',
                'options' => Mage::getModel('MarketPlace/Logs')->getScopes()                
        ));
        
        $this->addColumn('mp_is_error', array(
                'header' => Mage::Helper('Sales')->__('Error'),
                'index' => 'mp_is_error',
                'type' => 'options',
                'options' => Mage::getModel('MarketPlace/Logs')->getErrors(),
                'frame_callback' => array($this, 'decorateStatus')
        ));

        return parent::_prepareColumns();

    }

    /**
     * Get grid parent html
     * 
     * @return type 
     */
    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }

    /**
     * get grid url
     * 
     * @return string 
     */
    public function getGridUrl() {
        return $this->getUrl('MarketPlace/Logs/gridAjax', array('country_id' => Mage::registry('mp_country')->getId()));
    }
    
    /**
     * Decorate status
     *  
     * @param string $value
     * @param Varien_Object $row
     * @param type $column
     * @param type $isExport
     * @return string $cell 
     */
    public function decorateStatus($value, $row, $column, $isExport){
        
        switch($row->getmp_is_error()){
                       
            case 1:
                $cell = '<span class="grid-severity-critical"><span>'.$value.'</span></span>';
                break;
             case 0:
            default:    
                $cell = '<span class="grid-severity-notice"><span>'.$value.'</span></span>';
                break;
           
        }
        
        return $cell;
        
    }
    
    /**
     * Get countries
     * 
     * @return array $retour 
     */
    protected function _getCountries(){
        
        $retour = array();
        
        $collection = Mage::getModel('MarketPlace/Countries')->getCollection();
        
        foreach($collection as $item){
            
            // retrieve account
            $account = $item->getAccountByCountryId($item->getId());
            
            $retour[$item->getId()] = $account->getmpa_name().' - '.$item->getmpac_country_code();
            
        }
        
        return $retour;
        
    }

}
