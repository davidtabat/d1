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
 * @todo : useless ?
 */

class MDN_MarketPlace_Helper_Format extends Mage_Core_Helper_Abstract {

    /**
     * Format categories
     * 
     * @param string $str
     * @return string $retour 
     */
    public function formatCategories($str){

        $retour = "";
        $str = trim($str);
        $str = strtolower(utf8_decode($str));

        $pattern = array('#[éèêêë]#iu', '#[àâ]#iu', '#[îï]#iu', '#[ô]#iu', '#[ùû]#iu', '#[ \&/|>,\']#', '#"#');
        $replacement = array('e', 'a', 'i', 'o', 'u', '_', '');

        $retour = preg_replace($pattern, $replacement, trim($str));

        return $retour;

    }


}
