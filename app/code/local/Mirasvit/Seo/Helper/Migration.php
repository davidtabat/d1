<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Advanced SEO Suite
 * @version   1.3.5
 * @build     1248
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Seo_Helper_Migration
{
   /**
     * @param Mage_Core_Model_Resource_Setup $installer
     * @param string                         $table
     * @param string                         $columnName
     * @param string                         $columnType
     */
    public function addColumn($installer, $table, $columnName, $columnType)
    {
        $this->trySql($installer, "ALTER TABLE $table ADD $columnName $columnType");
    }

    /**
     * @param Mage_Core_Model_Resource_Setup $installer
     * @param string                         $sql
     */
    public function trySql($installer, $sql)
    {
        try {
            $installer->run($sql);
        } catch (Exception $e) {
            //            throw $e;
        }
    }
}
