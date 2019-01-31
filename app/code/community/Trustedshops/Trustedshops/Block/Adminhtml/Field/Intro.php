<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Adminhtml_Field_Intro
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render intro html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $imageContent = $this->getImageContent();
        $buttonContent = $this->getMemberButtonContent();

        return sprintf('<tr id="row_%s"><td colspan="5" class="value"><div id="%s">%s</div>%s</td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $imageContent, $buttonContent
        );
    }

    /**
     * get the member button html
     *
     * @return string
     */
    protected function getMemberButtonContent()
    {
        $trustedShopsLink = $this->getTrustedshopsRegistrationLink();
        $buttonText = Mage::helper('trustedshops')->__("Get your account");
        $memberLink = "window.open('" . $trustedShopsLink . "'); return false;";
        $buttonContent = '<button style="display:block; margin: 20px auto;" onclick="' . $memberLink . '"><span><span><span>' . $buttonText . '</span></span></span></button>';
        return $buttonContent;
    }

    /**
     * build the language specific url
     *
     * @return string
     */
    protected function getTrustedshopsRegistrationLink()
    {
        return Mage::helper('trustedshops')->__("http://www.trustbadge.com/en/pricing/")
        . '?utm_source=magento&utm_medium=software-app&utm_content=marketing-page&utm_campaign=magento-app';
    }

    /**
     * get the language specific intro image
     *
     * @return string
     */
    protected function getImageContent()
    {
        $imageFilename = Mage::helper('trustedshops')->__('trustedshops_en.jpg');
        $imageUrl = Mage::getDesign()->getSkinUrl('images/trustedshops/' . $imageFilename);
        $imageContent = '<img title="" alt="" src="' . $imageUrl . '" />';
        return $imageContent;
    }
}
