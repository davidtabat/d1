<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Adminhtml_Field_Integration
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render integration notice html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<tr id="row_%s"><td colspan="5" class="label"><span id="%s">%s <a href="%s" target="_blank">%s</a></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getComment(), $this->getLink(), $element->getLabel()
        );
    }

    /**
     * create param specific integration link
     *
     * @return string
     */
    protected function getLink()
    {
        $tsId = Mage::getStoreConfig('trustedshops/general/tsid');
        $language = Mage::app()->getLocale()->getLocaleCode();
        $software = "MAGENTO";
        $shopVersion = Mage::getVersion();
        $extensionVersion = (string)Mage::getConfig()->getNode('modules/Trustedshops_Trustedshops/version');
        return sprintf('https://www.trustedshops.com/integration/?shop_id=%s&backend_language=%s&shopsw=%s&shopsw_version=%s&plugin_version=%s&context=trustbadge&Google_Analytics',
            $tsId, $language, $software, $shopVersion, $extensionVersion
        );
    }
}
