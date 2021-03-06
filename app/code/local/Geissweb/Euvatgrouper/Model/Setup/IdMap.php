<?php
/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL:
 * http://www.geissweb.de/eula/
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @package     Geissweb_Euvatgrouper
 * @copyright   Copyright (c) 2011 GEISS Weblösungen (http://www.geissweb.de)
 * @license     http://www.geissweb.de/eula/ GEISSWEB End User License Agreement
 */

class Geissweb_Euvatgrouper_Model_Setup_IdMap extends Varien_Data_Form_Element_Abstract {

    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('select');
    }

    public function getElementHtml()
    {
        $helper = Mage::helper('euvatgrouper');
        $baseValues = $this->getBaseValues();
        $newValues = $this->getNewValues();

        $html = '<table id="'.$this->getName().'-table"><tr>';
        $html.= '<td>';
        $html.= '<strong>'.$helper->__('Old IDs').'</strong>';
        $html.= '</td>';
        $html.= '<td>';
        $html.= '<strong>'.$helper->__('New IDs').'</strong>';
        $html.= '</td>';
        $html.= '</tr>';

        foreach($baseValues as $base)
        {
            $html.= '<tr>';
            $html.= '<td>';
            $html.= "(ID ".$base['value'].") ".$base['label'];
            $html.= '</td>';
            $html.= '<td>';
            $html.= $this->renderSelect($base['value'], $this->getName(), $newValues);
            $html.= '</td>';
            $html.= '</tr>';
        }

        $html.= '</table>';
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    public function getLabelHtml($idSuffix = ''){
        if (!is_null($this->getLabel())) {
            $html = '<label for="'.$this->getHtmlId() . $idSuffix . '" style="'.$this->getLabelStyle().'">'.$this->getLabel()
                . ( $this->getRequired() ? ' <span class="required">*</span>' : '' ).'</label>'."\n";
        }
        else {
            $html = '';
        }
        return $html;
    }

    public function renderSelect($forValue, $name, array $data)
    {
        $html = '<select name="'.$name.'['.$forValue.']">';
        foreach($data as $key => $value)
        {
            $html.= '<option value="'.$key.'">'.$value.'</option>';
        }
        $html.= '</select>';
        return $html;
    }
}