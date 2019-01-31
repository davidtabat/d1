<?php
/**
 * Advance FAQ Management Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Advance FAQ Management
 * @author     Kamran Rafiq Malik <support@fmeextensions.com>
 *                          
 * 	       Asif Hussain <support@fmeextensions.com>
 * 	       1 - Created - 09-04-2012
 * 	       
 * @copyright  Copyright 2012 © www.fmeextensions.com All right reserved
 */
class FME_Faqs_Model_Source_Config_Selectedall
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'all', 'label'=>Mage::helper('adminhtml')->__('All')),
            array('value' => 'selected', 'label'=>Mage::helper('adminhtml')->__('Selected')),
        );
    }

}
