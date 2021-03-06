<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_View_Grid_Column_Renderer_DeveloperAction
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
    implements Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Interface
{
    //########################################

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $actions = array();

        $status = $row->getData('status');

        if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Ebay::NICK &&
            $row->getData('ebay_status')) {
            $status = $row->getData('ebay_status');
        }

        if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Amazon::NICK &&
            $row->getData('amazon_status')) {
            $status = $row->getData('amazon_status');
        }

        if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED ||
            $status == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('List'),
                'handler' => $this->getColumn()->getData('js_handler').'.actionHandler.listAction();'
            );
        }

        if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ||
            $status == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('Revise'),
                'handler' => $this->getColumn()->getData('js_handler').'.actionHandler.reviseAction();'
            );
        }

        if ($status != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED &&
            $status != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED &&
            $status != Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('Relist'),
                'handler' => $this->getColumn()->getData('js_handler').'.actionHandler.relistAction();'
            );
        }

        if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED ||
            $status != Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('Stop'),
                'handler' => $this->getColumn()->getData('js_handler').'.actionHandler.stopAction();'
            );
        }

        $actions[] = array(
            'title' => Mage::helper('M2ePro')->__('Stop & Remove'),
            'handler' => $this->getColumn()->getData('js_handler').'.actionHandler.stopAndRemoveAction();'
        );

        if ($row->getData('component_mode') == Ess_M2ePro_Helper_Component_Amazon::NICK) {
            $actions[] = array(
                'title' => Mage::helper('M2ePro')->__('Delete & Remove'),
                'handler' => $this->getColumn()->getData('js_handler').'.actionHandler.deleteAndRemoveAction();'
            );
        }

        $id = (int)$row->getData('id');
        $html = '';

        foreach ($actions as $action) {
            if ($html != '') {
                $html .= '<br/>';
            }

            $onclick = $this->getColumn()->getData('js_handler').'.selectByRowId(\''.$id.'\'); ' . $action['handler'];
            $html .= '<a href="javascript: void(0);" onclick="'.$onclick.'">'.$action['title'].'</a>';
        }

        // ---------------------------------------
        $colName = 'id';
        $url = $this->getUrl(
            '*/adminhtml_development_database/manageTable',
            array('table' => 'm2epro_listing_product',
                  'filter'=> base64_encode("{$colName}[from]={$id}&{$colName}[to]={$id}"))
        );
        $html .= '<br/><a href="'.$url.'" target="_blank" style="color: green;">Parent Product</a>';

        $colName = 'listing_product_id';
        Mage::app()->getCookie()->get('database_tables_merge_mode_cookie_key') && $colName = 'id';

        $componentMode = $row->getData('component_mode');
        $url = $this->getUrl(
            '*/adminhtml_development_database/manageTable',
            array('table' => "m2epro_{$componentMode}_listing_product",
                  'filter'=> base64_encode("{$colName}[from]={$id}&{$colName}[to]={$id}"))
        );
        $html .= '<br/><a href="'.$url.'" target="_blank" style="color: green;">Child Product</a>';
        // ---------------------------------------

        $html .= "<br/>{$id}";
        return $html;
    }

    //########################################
}
