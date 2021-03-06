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


class Mirasvit_Seo_Model_System_Config_Source_Conditionrichsnippets
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('seo')->__('Disabled')),
            array('value' => Mirasvit_Seo_Model_Config::CONDITION_RICH_SNIPPETS_NEW_ALL, 'label'=>Mage::helper('seo')->__('New for all products')),
            array('value' => Mirasvit_Seo_Model_Config::CONDITION_RICH_SNIPPETS_CONFIGURE, 'label'=>Mage::helper('seo')->__('Configure manually')),
        );
    }
}
