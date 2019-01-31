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


class Varien_Data_Form_Element_Imageviewerfaqs extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        
        $this->setType('textarea');
    }
    
    public function getElementHtml()
    {
        $url = Mage::getDesign()->getSkinUrl('images/');
        $jsUrl = Mage::getBaseUrl(MAGE_CORE_MODEL_STORE::URL_TYPE_JS);
        $this->addClass('input-text');
        
        $html = sprintf("<div id='%s' name='%s' size='6' style='width:278px !important; height:300px !important; border: 1px solid #C8C8C8; background:url(%s)'/>",$this->getHtmlId(),$this->getName(),$url.'faqs/theme1.jpg');
        
        $html .=sprintf("<script type='text/javascript'>
                                
                                Event.observe(window, 'load', function() {
                                    var theme_name = $('faqs_themes_select_theme').value;
                                    $('faqs_themes_selected_image').setStyle({background : 'url(".$url."faqs/'+theme_name+'.jpg)'});
                                    
                                });
                                
                                Event.observe(window, 'change', function() {
                                    var theme_name = $('faqs_themes_select_theme').value;
                                    $('faqs_themes_selected_image').setStyle({background : 'url(".$url."faqs/'+theme_name+'.jpg)'});
                                    
                                });
                                
                                
                        </script>",$this->getHtmlId()
                    );
                    
        $html .= $this->getAfterElementHtml();
        
        return $html;
    }
}