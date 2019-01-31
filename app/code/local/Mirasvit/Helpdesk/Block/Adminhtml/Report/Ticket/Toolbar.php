<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.2.4
 * @build     2266
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



/**
 * @method Varien_Data_Form getForm()
 * @method $this setForm(Varien_Data_Form $param)
 * @method Varien_Object getFilterData()
 * @method $this setFilterData(Varien_Object $param)
 */
class Mirasvit_Helpdesk_Block_Adminhtml_Report_Ticket_Toolbar extends Mage_Adminhtml_Block_Template
{
    public function _prepareLayout()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('toolbar_');
        $this->setForm($form);

        $this->setTemplate('mst_helpdesk/report/ticket/toolbar.phtml');

        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $this->_prepareFields();
        $this->_initFormValues();

        return parent::_beforeToHtml();
    }

    protected function _prepareFields()
    {
        $form = $this->getForm();

        $dateFormat = Mage::helper('helpdesk/report')->dateFormat();

        $form->addField('period', 'radios', array(
            'name' => 'period',
            'values' => array(
                array(
                    'value' => 'hour_of_day',
                    'label' => $this->__('Hour of day'),
                ),
                array(
                    'value' => 'day',
                    'label' => $this->__('Day'),
                ),
                array(
                    'value' => 'day_of_week',
                    'label' => $this->__('Day of week'),
                ),
                array(
                    'value' => 'month',
                    'label' => $this->__('Month'),
                ),
                array(
                    'value' => 'year',
                    'label' => $this->__('Year'),
                ),
            ),
            'label' => Mage::helper('helpdesk')->__('Period'),
            'value' => '1d',
        ));

        $form->addField('group_by', 'radios', array(
            'name' => 'group_by',
            'values' => array(
                array(
                    'value' => '',
                    'label' => $this->__('-'),
                ),
                array(
                    'value' => 'department',
                    'label' => $this->__('Department'),
                ),
                array(
                    'value' => 'agent',
                    'label' => $this->__('Agent'),
                ),
            ),
            'label' => Mage::helper('helpdesk')->__('Group by'),
            'value' => '1d',
        ));

        $form->addField('interval', 'select', array(
            'name' => 'interval',
            'values' => Mage::helper('helpdesk/report')->getIntervalsAsOptions(false, false, true),
            'label' => Mage::helper('helpdesk')->__('Range'),
        ));

        $form->addField('from', 'date', array(
            'name' => 'from',
            'format' => $dateFormat,
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'label' => Mage::helper('helpdesk')->__('From'),
        ));

        $form->addField('to', 'date', array(
            'name' => 'to',
            'format' => $dateFormat,
            'locale' => Mage::app()->getLocale()->getLocaleCode(),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'label' => Mage::helper('helpdesk')->__('To'),
        ));

        $this->setForm($form);
    }

    protected function _initFormValues()
    {
        $data = $this->getFilterData()->getData();

        $this->getForm()->addValues($data);

        return $this;
    }

    public function getIntervals()
    {
        $intervals = array();

        $format = Mage::helper('helpdesk/report')->dateFormat();

        foreach (Mage::helper('helpdesk/report')->getIntervals() as $code => $label) {
            $interval = Mage::helper('helpdesk/report')->getInterval($code);
            $intervals[$code] = array($interval->getFrom()->toString($format), $interval->getTo()->toString($format));
        }

        return $intervals;
    }

    public function getCustomElements()
    {
        $elements = array();

        foreach ($this->getForm()->getElements() as $element) {
            if (!in_array($element->getId(),
                    array('range', 'interval', 'from', 'to'))) {
                $elements[] = $element;
            }
        }

        return $elements;
    }
}
