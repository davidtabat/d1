<?php

class Zyelon_Kaufberatung_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();

        $template = Mage::getConfig()->getNode('global/page/layouts/two_columns_right/template');

        //echo $template;
        //die();

        $this->getLayout()->getBlock('root')->setTemplate('page/2columns-kaufberatung.phtml');

        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'kaufberatung', array('template' => 'kaufberatung/kaufberatung.phtml'));

        $this->getLayout()->getBlock('content')->append($block);

        
        //$form->setValues($data);

        $this->renderLayout();
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('kaufberatung/kaufberatung');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                // add code to send email
                
                $body = "";
                $body .= 'Name: ' . $data['Salutation'] . " " . $data['name'] . PHP_EOL;
                if ($data['company']  != '') {
                    $body .= 'Firma: ' . $data['company'] . PHP_EOL;
                }
                $body .= 'Telefon: ' . $data['phone'] . PHP_EOL;
                $body .= 'E-Mail-Adresse: ' . $data['email'];

                $body .= PHP_EOL . PHP_EOL;
                $body .= "Angaben zum Kopierer:" . PHP_EOL . PHP_EOL;
                $body .= "S/W oder Farbe: " . $data['color'] . PHP_EOL;
                $body .= "Formate: " . join(', ', $data['print']);
                if ($data['other_print'] != "") {
                    $body .= ', ' . $data['other_print'];
                }
                $body .= PHP_EOL;
                $body .= "Funktionen: " . join(', ', $data['features']) . PHP_EOL;
                $body .= "Papierfächer: " . $data['paper_trays'] . PHP_EOL;
                $body .= "Anschlüsse: " . $data['connect'] . PHP_EOL;
                $body .= "Anzahl Kopien / Monat: " . $data['volumes'] . PHP_EOL;
                $body .= "Tischgerät oder Standkopierer: " . $data['desktop'] . PHP_EOL . PHP_EOL;
                if ($data['budget']) {
                    $body .= "Budget in Euro: " . $data['question_budgetamount'] . PHP_EOL;
                } else {
                    $body .= "Keine Budgetvorgaben" . PHP_EOL;
                }
                if ($data['comments'] != "") {
                    $body .= PHP_EOL . "Sonstige Anmerkungen: " . PHP_EOL . $data['comments'] . PHP_EOL;
                }
                $body .= PHP_EOL;
                $body = utf8_decode($body);
                
                //echo $body; die;
                
                //$toEmail = "kontakt@kopiererhaus.de";
                $toEmail = "yash.zyelon@gmail.com";
                $toName = 'Kopiererhaus.de Kundenservice';

                //$body = "Hi there, here is some plaintext body content";
                $mail = Mage::getModel('core/email');
                $mail->setToName($toName);
                $mail->setToEmail($toEmail);
                $mail->setBody($body);
                $mail->setSubject('The Subject');
                $mail->setFromEmail('vikasp.zyelon@gmail.com');
                $mail->setFromName('vikas patel');
                $mail->setType('text'); // You can use 'html' or 'text'

                try {
                    $mail->send();
                    Mage::getSingleton('core/session')->addSuccess('Your request has been sent');
                    $this->_redirect('');
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError('Unable to send.');
                    $this->_redirect('');
                }

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('kaufberatung')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }
}
