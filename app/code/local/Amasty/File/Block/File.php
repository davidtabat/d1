<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_File
 */
class Amasty_File_Block_File extends Mage_Core_Block_Template 
{

    public function getCacheLifetime()
    {
        return 3600*24;
    }

    public function getCacheTags()
    {
        $tags = parent::getCacheTags();

        $tagName = $this->getCacheTagName();
        $tags[] = $tagName;

        return $tags;
    }

    public function getCacheTagName()
    {
        $productId = Mage::registry('current_product')->getId();
        return 'CATALOG_PRODUCT_' . $productId;
    }

    public function getCacheKeyInfo()
    {
        return array(
            'CATALOG_PRODUCT',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->isLoggedIn(),
            Mage::registry('current_product')->getId(),
        );
    }

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amfile/files.phtml');
    }

    protected function _toHtml()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $productId = Mage::registry('current_product')->getId();

        $files = Mage::getResourceModel('amfile/file_collection')->getFilesFrontend($productId, $storeId);

        if (sizeof($files) == 0) {
            return '';
        } else {
            $this->setFiles($files);
            return parent::_toHtml();
        }
    }

    public function getBlockTitle()
    {
        if ($this->hasData('block_title')) {
            $title = $this->getData('block_title');
        }
        else {
            $title = Mage::getStoreConfig('amfile/block/title');
        }

        return $title;
    }
}
