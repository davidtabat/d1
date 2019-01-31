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

interface MDN_MarketPlace_Helper_Interface_ProductCreation {

    /**
     * Mass product creation
     * 
     * @param Zend_Http_Request 
     */
    public function massProductCreation($request);

    /**
     * Build mass product file
     * 
     * @param Zend_Http_Request 
     */
    public function buildMassProductFile($request);

    /**
     * Send product file 
     */
    public function sendProductFile();

    /**
     * Check product creation 
     */
    public function checkProductCreation();

    /**
     * Import products
     * 
     * @param array $lines 
     */
    public function importProducts($lines);

    /**
     * Check if product file structure is ok
     * 
     * @paral array $lines 
     */
    public function isProductFileOk($lines);

    /**
     * Import created products 
     */
    public function importCreatedProducts();

}
