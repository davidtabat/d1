<?php
class Cmsmart_Configsystem_Block_Adminhtml_System_Config_Form_Field_Color extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    	$configValue = Mage::getStoreConfig('configsystem/cmsmart_backend/enabled_plugin_colorpicker');
    	// echo $configValue;
    	if($configValue == 1){
			$html = $element->getElementHtml(); //Default HTML
			$mcPath = $this->getJsUrl('cmsmart/jquery/plugins/mcolorpicker/');
			
			if (Mage::registry('jqueryLoaded') == false)
			{
				$html .= '';
				Mage::register('jqueryLoaded', 1);
	        }
			if (Mage::registry('colorPickerLoaded') == false)
			{
				$html .= '
				<script type="text/javascript" src="'. $mcPath .'mcolorpicker.min.js"></script>
				<script type="text/javascript">
					$j.fn.mColorPicker.init.replace = false;
					$j.fn.mColorPicker.defaults.imageFolder = "'. $mcPath .'images/";
					$j.fn.mColorPicker.init.allowTransparency = true;
					$j.fn.mColorPicker.init.showLogo = false;
				</script>
	            ';
				Mage::register('colorPickerLoaded', 1);
	        }
			
			$html .= '
				<script type="text/javascript">
					$j(function($){
						$("#'. $element->getHtmlId() .'").attr("data-hex", true).width("250px").mColorPicker();
					});
				</script>
	        ';
		
	        return $html;
	     }
    }
}
