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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_MarketPlace
 * @version 2.1
 */
class MDN_MarketPlace_Helper_Serializer extends Mage_Core_Helper_Abstract {

    /**
     * Serialize object
     * 
     * @param object $object
     * @return string 
     */
    public function serializeObject($object) {
        return urlencode(serialize($object));
    }

    /**
     * Unserialize object
     * 
     * @param string $raw
     * @return object 
     */
    public function unserializeObject($raw) {
        return unserialize(urldecode($raw));
    }

}