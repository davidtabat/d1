<?php

class VladimirPopov_WebFormsMirasvitHD_Model_Observer
{
    public function convertToTicket($observer)
    {
        if (!Mage::getStoreConfig('webforms/helpdesk/enable')) return;

        if (Mage::registry('webformsmirasvithd_ticket_save')) return;
        Mage::register('webformsmirasvithd_ticket_save', true);


        $webform = Mage::getModel('webforms/webforms')->load($observer->getResult()->getWebformId());
        if ($webform->getData('mirasvithd_create_tickets'))
            Mage::helper('webformsmirasvithd')->convertToTicket($observer->getResult()->getId());

    }

    public function addSettings($observer)
    {

        $form = $observer->getForm();

        $fieldset = $form->addFieldset('webformsmirasvithd_setting', array(
            'legend' => Mage::helper('webformsmirasvithd')->__('Mirasvit Help Desk Integration')
        ));


        $fieldset->addField('mirasvithd_create_tickets', 'select', array(
            'label' => Mage::helper('webformsmirasvithd')->__('Create tickets'),
            'title' => Mage::helper('webformsmirasvithd')->__('Create tickets'),
            'name' => 'mirasvithd_create_tickets',
            'required' => false,
            'note' => Mage::helper('webformsmirasvithd')->__('Create new tickets from results'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $default = array('0' => Mage::helper('helpdesk')->__('Default'));
        $departments = array();
        $departments_collection = Mage::getModel('helpdesk/department')->getCollection()->setOrder('sort_order', 'asc');
        foreach ($departments_collection as $department) {
            $departments[$department->getId()] = $department->getName();
        }
        $department_options = $default + $departments;

        $fieldset->addField('mirasvithd_default_department', 'select', array(
            'label' => Mage::helper('webformsmirasvithd')->__('Default department'),
            'title' => Mage::helper('webformsmirasvithd')->__('Default department'),
            'name' => 'mirasvithd_default_department',
            'required' => false,
            'note' => Mage::helper('webformsmirasvithd')->__('Set default department'),
            'values' => $department_options,
        ));

    }

    public function addMassAction($observer)
    {

        $grid = $observer->getGrid();

        $departments = Mage::getModel('helpdesk/department')->toOptionArray();
        $department_options = $departments;

        $grid->getMassactionBlock()->addItem('convert_to_tickets', array(
            'label' => Mage::helper('webformsmirasvithd')->__('Convert to tickets'),
            'url' => $grid->getUrl('webformsmirasvithd/adminhtml_results/massConvertToTickets', array('webform_id' => $grid->getRequest()->getParam('webform_id'))),
            'confirm' => Mage::helper('webformsmirasvithd')->__('Convert selected results to help desk tickets?'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'department_id',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('webformsmirasvithd')->__('Department'),
                    'values' => $department_options
                )
            )

        ));

    }

    public function addFieldTypes($observer)
    {
        $types = $observer->getTypes();

        $types->addData(array(
            'helpdesk/departments' => Mage::helper('webformsmirasvithd')->__('Help Desk / Departments'),
            'helpdesk/orders' => Mage::helper('webformsmirasvithd')->__('Help Desk / Orders'),
            'helpdesk/priority' => Mage::helper('webformsmirasvithd')->__('Help Desk / Priority'),
        ));
    }

    public function fieldsToHtml($observer)
    {
        $field = $observer->getField();
        $html_object = $observer->getHtmlObject();
        $html = $html_object->getHtml();

        $field_id = "field[" . $field->getId() . "]";
        $field_name = $field_id;
        $field_style = '';
        $field_class = 'required-entry';
        if ($field->getCssStyle()) {
            $field_style = $field->getCssStyle();
        }

        switch ($field->getType()) {
            case 'helpdesk/priority':
                $collection = Mage::getModel('helpdesk/priority')->toOptionArray();
                $html = "<select name='$field_name' id='$field_id' class='$field_class' style='$field_style'>";
                foreach ($collection as $option) {
                    $selected = '';
                    $html .= "<option value=\"" . $option['value'] . "\" {$selected}>{$option['label']}</option>";
                }
                $html .= "</select>";
                break;
            case 'helpdesk/departments':
                $collection = Mage::getModel('helpdesk/department')->toOptionArray();
                $html = "<select name='$field_name' id='$field_id' class='$field_class' style='$field_style'>";
                foreach ($collection as $option) {
                    $html .= "<option value=\"" . $option['value'] . "\">{$option['label']}</option>";
                }
                $html .= "</select>";
                break;
            case 'helpdesk/orders':
                if (Mage::helper('customer')->isLoggedIn()) {
                    $collection = Mage::getModel('sales/order')->getCollection()->addFilter('customer_id', Mage::getSingleton('customer/session')->getCustomerId());
                    $collection->getSelect()->order('created_at desc');
                    $html = "<select name='$field_name' id='$field_id' class='$field_class' style='$field_style'>";
                    $html .= "<option value=''>" . Mage::helper('helpdesk')->__('--- Select an order ---') . "</option>";
                    $str = ('#%s at %s (%s)');
                    foreach ($collection as $order) {
                        $html .= "<option value=\"" . $order->getId() . "\">" . Mage::helper('helpdesk')->__($str, $order->getIncrementId(), Mage::helper('core')->formatDate($order->getCreatedAt()), Mage::helper('core')->formatPrice($order->getGrandTotal())) . "</option>";
                    }
                    $html .= "</select>";
                }
                break;
        }

        $html_object->setHtml($html);
    }

    public function prepareColumnsConfig($observer)
    {
        $field = $observer->getField();
        $config = $observer->getConfig();

        switch ($field->getType()) {
            case 'helpdesk/priority':
                $config->setData('type', 'options');
                $config->setData('options', Mage::getModel('helpdesk/priority')->getCollection()->getOptionArray());
                $config->setData('renderer',false);
                break;
            case 'helpdesk/departments':
                $config->setData('type', 'options');
                $config->setData('options', array_merge(array(0 => Mage::helper('helpdesk')->__('Default')), Mage::getModel('helpdesk/department')->getCollection()->getOptionArray()));
                $config->setData('renderer',false);
                break;
            case 'helpdesk/orders':
                $config->setData('type', 'number');
                break;
        }
    }

    public function resultsRenderer($observer)
    {
        $field = $observer->getField();
        $html_object = $observer->getHtmlObject();
        $value = $observer->getValue();

        switch ($field->getType()) {
            case 'helpdesk/priority':
                $options = Mage::getModel('helpdesk/priority')->getCollection()->getOptionArray();
                if (!empty($options[$value]))
                    $html_object->setData('html', $options[$value]);
                break;
            case 'helpdesk/departments':
                $options = Mage::getModel('helpdesk/department')->getCollection()->getOptionArray();
                if (!empty($options[$value]))
                    $html_object->setData('html', $options[$value]);
                break;
            case 'helpdesk/orders':
                $order = Mage::getModel('sales/order')->load($value);
                $html_object->setData('html', $order->getIncrementId());
                break;
        }

    }
}