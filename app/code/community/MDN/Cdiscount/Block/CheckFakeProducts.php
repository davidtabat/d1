<?php


class MDN_Cdiscount_Block_CheckFakeProducts extends Mage_Adminhtml_Block_Widget_Form {

    protected function _toHtml() {
        $html = ' ';

        $skus = array('INTERETBCA', 'FRAISTRAITEMENT');
        foreach($skus as $sku)
        {
            if (!Mage::getModel('catalog/product')->getIdBySku($sku))
                $html .= '<div class="notification-global"><font color=red><b>'.$this->__('You must create SKU %s for Cdiscount extension ! (<a href="%s">See documentation</a>)', $sku, 'http://www.boostmyshop.com/docs/controller.php?action=index&autoSelectPath=%2Fdocs%2FCdiscount%2F2.+Installation%2F').'</b></font></div>';
        }

        return $html;
    }

}