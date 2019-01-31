<?php

class VladimirPopov_WebFormsMirasvitHD_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function convertToTicket($result_id)
    {
        $result = Mage::getModel('webforms/results')->load($result_id);

        $params = array();
        $params['name'] = $result->getEmailSubject();

        // process content
        $content = $result->toHtml('admin', array(
            'header' => false,
            'skip_fields' => array(
                'helpdesk/priority',
                'helpdesk/orders',
                'helpdesk/departments',
                'file',
                'image',
            )
        ));
        $params['message'] = $content;

        if ($this->getResultPriority($result)) {
            $params['priority_id'] = $this->getResultPriority($result);
        }
        if ($this->getResultDepartmentId($result)) {
            $params['department_id'] = $this->getResultDepartmentId($result);
        }
        if ($this->getResultOrderId($result)) {
            $params['order_id'] = $this->getResultOrderId($result);
        }

        $params['customer_name'] = $result->getCustomerName();
        $params['customer_email'] = $result->getCustomerEmail();
        $post = $params;
        $channel =  Mirasvit_Helpdesk_Model_Config::CHANNEL_CONTACT_FORM;

        $ticket = Mage::getModel('helpdesk/ticket');
        $customer = Mage::helper('helpdesk/customer')->getCustomerByPost($post);

        $ticket->setCustomerId($customer->getId())
            ->setCustomerEmail($customer->getEmail())
            ->setCustomerName($customer->getName())
            ->setQuoteAddressId($customer->getQuoteAddressId())
            ->setCode(Mage::helper('helpdesk/string')->generateTicketCode())
            ->setName($post['name'])
            ->setDescription(Mage::helper('helpdesk/process')->getEnviromentDescription());

        if (isset($post['priority_id'])) {
            $ticket->setPriorityId((int)$post['priority_id']);
        }
        if (isset($post['department_id'])) {
            $ticket->setDepartmentId((int)$post['department_id']);
        } else {
            $ticket->setDepartmentId($this->getConfig()->getContactFormDefaultDepartment());
        }
        if (isset($post['order_id'])) {
            $ticket->setOrderId((int)$post['order_id']);
        }
        $ticket->setStoreId($result->getStoreId());
        $ticket->setChannel($channel);

        Mage::helper('helpdesk/field')->processPost($post, $ticket);
        $ticket->save();
        $body = $post['message'];
        $ticket->addMessage($body, $customer, false, Mirasvit_Helpdesk_Model_Config::CUSTOMER, Mirasvit_Helpdesk_Model_Config::MESSAGE_PUBLIC, false, Mirasvit_Helpdesk_Model_Config::FORMAT_PLAIN);
        Mage::helper('helpdesk/history')->changeTicket($ticket, Mirasvit_Helpdesk_Model_Config::CUSTOMER, array('customer' => $customer));

        $message = $ticket->getLastMessage();
        $message->setBodyFormat('TEXT/HTML');
        $message->save();

        $files = $result->getFiles();
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($files) {
            foreach ($files as $file) {
                Mage::getModel('helpdesk/attachment')
                    ->setName($file['name'])
                    ->setType(strtoupper(finfo_file($finfo,$file['path'])))
                    ->setSize(filesize($file['path']))
                    ->setBody(addslashes(file_get_contents($file['path'])))
                    ->setMessageId($message->getId())
                    ->save();
            }
        }
    }

    public function getResultPriority($result)
    {
        if (is_int($result)) {
            $result = Mage::getModel('webforms/results')->load($result);
        }

        if ($result->getId()) {
            $fields = Mage::getModel('webforms/fields')->getCollection()->addFilter('webform_id', $result->getWebformId())->addFilter('type', 'helpdesk/priority');

            $priority = 0;

            foreach ($result->getData() as $key => $value) {
                if ($key == 'field_' . $fields->getFirstItem()->getId()) {
                    $priority = $value;
                }
            }
            return $priority;
        }
    }

    public function getResultDepartmentId($result)
    {
        if (Mage::app()->getRequest()->getParam('department_id')) {
            return Mage::app()->getRequest()->getParam('department_id');
        }

        if (is_int($result)) {
            $result = Mage::getModel('webforms/results')->load($result);
        }

        if ($result->getId()) {
            $fields = Mage::getModel('webforms/fields')
                ->setStoreId($result->getStoreId())
                ->getCollection()
                ->addFilter('webform_id', $result->getWebformId())
                ->addFilter('type', 'helpdesk/departments');

            $department_id = 0;

            foreach ($result->getData() as $key => $value) {
                if ($key == 'field_' . $fields->getFirstItem()->getId()) {
                    $department_id = $value;
                }
            }

            if ($department_id == 0) {
                $department_id = Mage::getModel('webforms/webforms')
                    ->setStoreId($result->getStoreId())
                    ->load($result->getWebformId())
                    ->getMirasvithdDefaultDepartment();
            }

            return intval($department_id);
        }
    }

    public function getResultOrderId($result)
    {
        if (is_int($result)) {
            $result = Mage::getModel('webforms/results')->load($result);
        }

        if ($result->getId()) {
            $fields = Mage::getModel('webforms/fields')
                ->setStoreId($result->getStoreId())
                ->getCollection()
                ->addFilter('webform_id', $result->getWebformId())
                ->addFilter('type', 'helpdesk/orders');

            $order_id = 0;

            foreach ($result->getData() as $key => $value) {
                if ($key == 'field_' . $fields->getFirstItem()->getId()) {
                    $order_id = $value;
                }
            }

            return $order_id;
        }
    }
}