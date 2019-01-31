<?php

class MDN_Cdiscount_Block_Widget_Grid_Column_Renderer_BrandAssociation extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row){

        $divId = 'mp_brand_cdiscount_'.$row->getoption_id();
        $html = '<div id="'.$divId.'">';

        $cdiscountCategory = Mage::getSingleton('MarketPlace/Brands')->getBrandForManufacturer('cdiscount', $row->getoption_id());

        if ($cdiscountCategory)
            $html .= '<font color="green">'.$cdiscountCategory->getmpb_label();
        else
            $html .= '<font color="red">'.'not associated';

        $html .= '</font>';

        $onClick = "new Ajax.Updater('".$divId."', '".$this->getUrl('Cdiscount/Brands/Edit', array('mp' => 'cdiscount', 'manufacturer' => $row->getoption_id()))."', {method: 'get'});";

        $html .= '&nbsp;<input type="button" value="Edit" onclick="'.$onClick.'">';

        $html .= '</div>';

        return $html;

    }

}
