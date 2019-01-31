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
 * @package MDN_Cdiscount
 * @version 2.0
 */
class MDN_Cdiscount_Block_Grids_ProductsCreatedWaitingForUpdate extends MDN_Cdiscount_Block_Grids_ProductsCreated {
    
    /**
     * Construct 
     */
    public function __construct() {

        parent::__construct();
        $this->setId('waiting_for_update_products_grid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('Aucun elt');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * get condition
     * 
     * @return string 
     */
    protected function _getCondition() {
        return ($this->getCountry()->getId()) ? 'mp_marketplace_id="' . $this->getCountry()->getId() . '" AND mp_marketplace_status="created" AND updated_at > mp_last_update' : '1';
    }

    /**
     * get grid url (ajax use) 
     */
    public function getGridUrl() {
        return $this->getUrl('Cdiscount/Main/productsCreatedWaitingForUpdateGridAjax', array('_current' => true, 'country_id' => $this->getCountry()->getId()));
    }
    
}
