<?php

/**
 * @category  Trustedshops
 * @package   Trustedshops_Trustedshops
 * @author    Trusted Shops GmbH
 * @copyright 2016 Trusted Shops GmbH
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.trustedshops.de/
 */
class Trustedshops_Trustedshops_Block_Adminhtml_Field_Notice
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render notice html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $comment = $element->getComment();
        // check if comments have a parameter
        if (strpos($comment, '%s') !== false) {
            // translate parameter
            $comment = sprintf($comment,
                Mage::helper('trustedshops')->__($element->getOriginalData('comment_param'))
            );
        }

        return sprintf('<tr id="row_%s"></td><td colspan="5" class="value"><span id="%s">%s</td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), nl2br($comment)
        );
    }
}
