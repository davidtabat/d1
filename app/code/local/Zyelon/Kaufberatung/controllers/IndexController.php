<?php

class Zyelon_Kaufberatung_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();

        $template = Mage::getConfig()->getNode('global/page/layouts/two_columns_right/template');

        //echo $template;
        //die();

        $this->getLayout()->getBlock('root')->setTemplate('page/2columns-kaufberatung.phtml');

        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'kaufberatung', array('template' => 'kaufberatung/form.phtml'));

        $this->getLayout()->getBlock('content')->append($block);

        $form = $this->getForm();
        $formSuccess = false;

        $params = $this->getRequest()->getParams();
        $form->populate($params);
        //$form->setValues($data);
        $block->assign("formSuccess", $formSuccess);
        $block->assign("form", $form);

        $this->renderLayout();
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('kaufberatung/kaufberatung');
            $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));
            
            $form = $this->getForm();
            $form->populate($data);
            
            /*echo '<pre>';
            print_r($model);
            die;*/
            
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
                $body .= 'Name: ' . $form->getValue('title') . " " . $form->getValue('name') . PHP_EOL;
                if ($form->getValue('company') != '') {
                    $body .= 'Firma: ' . $form->getValue('company') . PHP_EOL;
                }
                $body .= 'Telefon: ' . $form->getValue('phone') . PHP_EOL;
                $body .= 'E-Mail-Adresse: ' . $form->getValue('email');

                $body .= PHP_EOL . PHP_EOL;
                $body .= "Angaben zum Kopierer:" . PHP_EOL . PHP_EOL;
                $body .= "S/W oder Farbe: " . $form->getValue('color') . PHP_EOL;
                $body .= "Formate: " . join(', ', $form->getValue('print'));
                if ($form->getValue('other_print') != "") {
                    $body .= ', ' . $form->getValue('other_print');
                }
                $body .= PHP_EOL;
                $body .= "Funktionen: " . join(', ', $form->getValue('features')) . PHP_EOL;
                $body .= "Papierfächer: " . $form->getValue('paper_trays') . PHP_EOL;
                $body .= "Anschlüsse: " . $form->getValue('connect') . PHP_EOL;
                $body .= "Anzahl Kopien / Monat: " . $form->getValue('volumes') . PHP_EOL;
                $body .= "Tischgerät oder Standkopierer: " . $form->getValue('desktop') . PHP_EOL . PHP_EOL;
                if ($form->getValue('budget')) {
                    $body .= "Budget in Euro: " . $form->getValue('question_budgetamount') . PHP_EOL;
                } else {
                    $body .= "Keine Budgetvorgaben" . PHP_EOL;
                }
                if ($form->getValue('comments') != "") {
                    $body .= PHP_EOL . "Sonstige Anmerkungen: " . PHP_EOL . $form->getValue('comments') . PHP_EOL;
                }
                $body .= PHP_EOL;
                $body = utf8_decode($body);
                //echo $body; die;
                
                $toEmail = "kontakt@kopiererhaus.de";
                //$toEmail = "yash.zyelon@gmail.com";
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
                    Mage::getSingleton('core/session')->addSuccess('Ihre Anfrage wurde versendet.');
                    $this->_redirect('');
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError('Unable to send request.');
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
    
    protected function getForm() {

        $form = new Zend_Form();

        $questionType = new Zend_Form_Element_Radio('color');
        $questionType->addFilter('StripTags');
        $questionType->addFilter('StringTrim');
        $questionType->setRequired(false);
        $questionType->addMultiOptions(array(
            'Schwarz / Weiß' => 'Schwarz / Weiß',
            'Farbe und S/W' => 'Farbe und S/W',
        ));

        $questionVolume = new Zend_Form_Element_Radio('volumes');
        $questionVolume->addFilter('StripTags');
        $questionVolume->addFilter('StringTrim');
        $questionVolume->setRequired(false);
        $questionVolume->addMultiOptions(array(
            'bis zu 2.500' => 'bis zu 2.500',
            '2.500 - 5.000' => '2.500 - 5.000',
            '5.000 - 10.000' => '5.000 - 10.000',
            '10.000 - 25.000' => '10.000 - 25.000',
            'mehr als 25.000' => 'mehr als 25.000',
        ));

        $questionFunction = new Zend_Form_Element_MultiCheckbox('features');
        $questionFunction->addFilter('StripTags');
        $questionFunction->addFilter('StringTrim');
        $questionFunction->setRequired(false);
        $questionFunction->addMultiOptions(array(
            'Kopieren' => 'Kopieren',
            'Drucken' => 'Drucken',
            'Faxen' => 'Faxen',
            'Scannen' => 'Scannen',
            'Duplex-Druck/-Scan (Beidseitig)' => 'Duplex-Druck/-Scan (Beidseitig)',
            'Lochen und heften (Finisher)' => 'Lochen und heften (Finisher)',
            'Stapeln und sortieren (Sorter)' => 'Stapeln und sortieren (Sorter)',
        ))->setValue('Kopieren');

        $questionPaper = new Zend_Form_Element_Radio('paper_trays');
        $questionPaper->addFilter('StripTags');
        $questionPaper->addFilter('StringTrim');
        $questionPaper->setRequired(false);
        $questionPaper->addMultiOptions(array(
            '2' => '2',
            '4' => '4',
            'mehr' => 'mehr',
            'weiß ich nicht' => 'weiß ich nicht',
        ));

        $questionConnect = new Zend_Form_Element_Radio('connect');
        $questionConnect->addFilter('StripTags');
        $questionConnect->addFilter('StringTrim');
        $questionConnect->setRequired(false);
        $questionConnect->addMultiOptions(array(
            'Netzwerk' => 'Netzwerk',
            'Lokal' => 'Lokal',
            'Standalone' => 'Standalone',
            'weiß ich nicht' => 'weiß ich nicht',
        ));

        $questionUnit = new Zend_Form_Element_Radio('desktop');
        $questionUnit->addFilter('StripTags');
        $questionUnit->addFilter('StringTrim');
        $questionUnit->setRequired(false);
        $questionUnit->addMultiOptions(array(
            'Tischgerät' => 'Tischgerät',
            'Standkopierer' => 'Standkopierer',
            'weiß ich nicht' => 'weiß ich nicht',
        ));

        $questionFormat = new Zend_Form_Element_MultiCheckbox('print');
        $questionFormat->addFilter('StripTags');
        $questionFormat->addFilter('StringTrim');
        $questionFormat->setRequired(false);
        $questionFormat->addMultiOptions(array(
            'A4' => 'A4',
            'A3' => 'A3',
            'Etiketten' => 'Etiketten',
            'Broschüren' => 'Broschüren',
        ))->setValue('A4');

        $questionFormatMisc = new Zend_Form_Element_Text('other_print');
        $questionFormatMisc->addFilter('StripTags');
        $questionFormatMisc->addFilter('StringTrim');
        $questionFormatMisc->setRequired(false);

        $questionBudgetAmount = new Zend_Form_Element_Text('question_budgetamount');
        $questionBudgetAmount->addFilter('StripTags');
        $questionBudgetAmount->addFilter('StringTrim');
        $questionBudgetAmount->setRequired(false);
        //$questionBudgetAmount -> setAttrib('class', 'required-entry');

        $questionBudget = new Zend_Form_Element_Radio('budget');
        $questionBudget->addFilter('StripTags');
        $questionBudget->addFilter('StringTrim');
        $questionBudget->setRequired(false);
        $questionBudget->addMultiOptions(array(
            1 => 'Budget ca.',
            0 => 'Keine Budgetvorgaben',
        ))->setValue(1);

        $questionNote = new Zend_Form_Element_Textarea('comments');
        $questionNote->addFilter('StripTags');
        $questionNote->addFilter('StringTrim');
        $questionNote->setRequired(false);
        //$questionNote -> setAttrib('class', 'required-entry');

        $title = new Zend_Form_Element_Select('title');
        $title->addFilter('StripTags');
        $title->addFilter('StringTrim');
        $title->setRequired(true);
        $title->setAllowEmpty(false);
        $title->addMultiOptions(array(
            'Herr' => 'Herr',
            'Frau' => 'Frau',
        ));
        $title->setAttrib('class', 'required-entry');

        $name = new Zend_Form_Element_Text('name');
        $name->addFilter('StripTags');
        $name->addFilter('StringTrim');
        $name->setRequired(true);
        $name->setAllowEmpty(false);
        $name->setAttrib('class', 'required-entry');

        $company = new Zend_Form_Element_Text('company');
        $company->addFilter('StripTags');
        $company->addFilter('StringTrim');
        $company->setRequired(false);
        $company->setAllowEmpty(true);

        $phone = new Zend_Form_Element_Text('phone');
        $phone->addFilter('StripTags');
        $phone->addFilter('StringTrim');
        $phone->setRequired(true);
        $phone->setAllowEmpty(false);
        $phone->setAttrib('class', 'required-entry');

        $email = new Zend_Form_Element_Text('email');
        $email->addFilter('StripTags');
        $email->addFilter('StringTrim');
        $email->setRequired(true);
        $email->setAllowEmpty(false);
        $email->setAttrib('class', 'required-entry validate-email');

        $form->addElements(array(
            $questionType,
            $questionFormat,
            $questionVolume,
            $questionBudget,
            $questionBudgetAmount,
            $questionFormatMisc,
            $questionPaper,
            $questionFunction,
            $questionNote,
            $questionConnect,
            $questionUnit,
            $title,
            $name,
            $company,
            $phone,
            $email
        ));

        $form->setElementDecorators(array(
            'ViewHelper',
            'Label'
        ));

        return $form;
    }
}
