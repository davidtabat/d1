<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Model_Mysql4_Stat extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('amfile/stat', 'id');
    }

    public function deleteStat()
    {
        $this->_getWriteAdapter()->truncate($this->getMainTable());
    }
}
