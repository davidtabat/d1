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
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Nicolas MUGNIER
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MDN_Cdiscount_Helper_Multiaccount extends MDN_MarketPlace_Helper_Multiaccount {

    protected $_mp = 'cdiscount';

    /**
     * Get accounts
     *
     * @return array
     */
    public function getAccounts(){
        $allAccounts = $this->getAllAccounts();

        return array(
            self::kIndexFirstAccount => $allAccounts[self::kIndexFirstAccount],
            self::kIndexSecondAccount => $allAccounts[self::kIndexSecondAccount]
        );
    }

}
