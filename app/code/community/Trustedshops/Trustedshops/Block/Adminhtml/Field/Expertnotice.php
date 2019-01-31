<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Adminhtml_Field_Expertnotice
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render notice for expert mode
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $helper = Mage::helper('trustedshops');

        // build the base html structure
        $noticeTml = '<div>';
        $noticeTml .= '	<span>%s</span>';
        $noticeTml .= '	<ul style="list-style: disc; margin-left: 20px;">';
        $noticeTml .= '		<li>%s</li>';
        $noticeTml .= '		<li>%s</li>';
        $noticeTml .= '		<li>%s</li>';
        $noticeTml .= '	</ul>';
        $noticeTml .= '	<span>%s</span>';
        $noticeTml .= '</div>';

        // create the language specific links
        $noticeLinkHtml = $helper->__('Learn more about %s options and %s configuration.');
        $noticeLinkHtml = sprintf($noticeLinkHtml,
            '<a target="_blank" href="' . $helper->__('http://www.trustedshops.co.uk/support/trustbadge/trustbadge-custom/') . '">' . $helper->__('Trustbadge') . '</a>',
            '<a target="_blank" href="' . $helper->__('http://www.trustedshops.co.uk/support/product-reviews/') . '">' . $helper->__('Product Reviews') . '</a>'
        );

        // put everything together
        $notice = sprintf($noticeTml,
            $helper->__('Use additional options to customize your Trusted Shops Integration or use the latest code version here. E.g.:'),
            $helper->__('Place your Trustbadge wherever you want'),
            $helper->__('Deactivate mobile use'),
            $helper->__('Jump from your Product Reviews stars directly to your Product Reviews'),
            $noticeLinkHtml
        );

        return sprintf('<tr id="row_%s"></td><td colspan="5" class="value"><span id="%s">%s</td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), nl2br($notice)
        );
    }
}
