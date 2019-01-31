<?php
 /**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */ 
class Amasty_File_Model_Mysql4_StoreCustomerGroup extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('amfile/store_customer_group', 'id');
    }
}