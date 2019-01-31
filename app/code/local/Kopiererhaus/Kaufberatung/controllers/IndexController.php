<?php

class Kopiererhaus_Kaufberatung_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();

        $template = Mage::getConfig()->getNode('global/page/layouts/two_columns_right/template');

        //echo $template;
        //die();

        $this->getLayout()->getBlock('root')->setTemplate('page/2columns-kaufberatung.phtml');

        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template', 'kaufberatung', array('template' => 'kaufberatung/form.phtml'));

        $this->getLayout()->getBlock('content')->append($block);

        $form = $this->getForm();
        $params = $this->getRequest()->getParams();
        $form->populate($params);
        $formSuccess = false;
        $block->assign("formSuccess", $formSuccess);
        $block->assign("form", $form);

        $this->renderLayout();
    }
    public function submitAction(){
        $form = $this->getForm();
        $formSuccess = false;
        $params = $this->getRequest()->getParams();
        $form->populate($params);
        if (isset($_POST) && isset($form)) {
            $fromEmail = $form->getValue('email');
            $fromName = $form->getValue('title') . ' ' . $form->getValue('name');

            //$toEmail = "kontakt@kopiererhaus.de";
            //$toName = 'Kopiererhaus.de Kundenservice';

            $body = "";
            
            $body .= "Sehr geehrte Damen und Herren,<br />" . PHP_EOL;
            
            $body .= "<br />". PHP_EOL;
            
            $body .= "wir haben Ihre Anfrage erhalten und werden uns in Kürze wieder bei Ihnen melden. Bitte geben Sie uns bis dahin etwas Zeit ein für Sie passendes Gerät zu recherchieren.<br />". PHP_EOL;
            
            $body .= "Vielen Dank.<br />". PHP_EOL;
            $body .= "<br />". PHP_EOL;
            $body .= "Dies ist eine Kopie Ihrer E-Mail an kontakt@kopiererhaus.de:<br />" . PHP_EOL;
            
            $body .= "<br />". PHP_EOL;
            
            $body .="----------------------------------------------------------------------------------------------------------------------------<br />". PHP_EOL;
            
            $body .= "<br />". PHP_EOL;
            
            $body .= 'Name: ' . $form->getValue('title') . " " . $form->getValue('name') ."<br />" . PHP_EOL;
            if ($form->getValue('company') != '') {
                $body .= 'Firma: ' . $form->getValue('company') . "<br />" . PHP_EOL;
            }
            $body .= 'Telefon: ' . $form->getValue('phone') . "<br />" . PHP_EOL;
            $body .= 'E-Mail-Adresse: ' . $form->getValue('email') . "<br />";

            $body .= "<br />" . PHP_EOL . "<br />" . PHP_EOL;
            $body .= "Angaben zum Kopierer:" . "<br /><br />" . PHP_EOL . PHP_EOL  ;
            $body .= "S/W oder Farbe: " . $form->getValue('color') . "<br />" . PHP_EOL ;
            $body .= "Formate: " . join(', ', $form->getValue('print'));
            if ($form->getValue('other_print') != "") {
                $body .= ', ' . $form->getValue('other_print');
            }
            $body .=  "<br />" . PHP_EOL;
            $body .= "Funktionen: " . join(', ', $form->getValue('features')). "<br />" . PHP_EOL;
            $body .= "Papierfächer: " . $form->getValue('paper_trays'). "<br />" . PHP_EOL;
            $body .= "Anschlüsse: " . $form->getValue('connect'). "<br />" . PHP_EOL;
            $body .= "Anzahl Kopien / Monat: " . $form->getValue('volumes'). "<br />" . PHP_EOL;
            $body .= "Tischgerät oder Standkopierer: " . $form->getValue('desktop'). "<br />" . PHP_EOL . PHP_EOL;
            if ($form->getValue('budget')) {
                $body .= "Budget in Euro: " . $form->getValue('question_budgetamount') . "<br />" . PHP_EOL;
            } else {
                $body .= "Keine Budgetvorgaben" . "<br />" . PHP_EOL;
            }
            if ($form->getValue('comments') != "") {
                $body .= PHP_EOL . "Sonstige Anmerkungen: " . "<br />" . PHP_EOL . $form->getValue('comments') . "<br />" . PHP_EOL;
            }
            
            $body .= "<br />". PHP_EOL;
            $body .= "<br />". PHP_EOL;
            
            $body .="----------------------------------------------------------------------------------------------------------------------------<br />". PHP_EOL;
            $body .= "<br />". PHP_EOL;
            
            $body .= "Mit freundlichen Grüßen / Best regards<br />". PHP_EOL;
            $body .= "<br />". PHP_EOL;
            $body .= "Büroservice Hübner GmbH<br />". PHP_EOL . "Vor dem Großholz 1<br />". PHP_EOL ."72074 Tübingen<br />". PHP_EOL ."Deutschland<br />". PHP_EOL; 
            
            $body .= "<br />". PHP_EOL;
            
            $body .= "Telefon: 07071 966 9000<br />". PHP_EOL . "Telefax: 07071 966 9090<br />". PHP_EOL . "E-Mail: kontakt@kopiererhaus.de<br />". PHP_EOL;
            $body .= "<br />". PHP_EOL;
            $body .= "Web: http://www.kopiererhaus.de<br />". PHP_EOL; 
            $body .= "<br />". PHP_EOL;
            $body .= "vertreten durch die Geschäftsführer Juliane Hübner, Michael Hübner<br />". PHP_EOL;
            $body .= "eingetragen im Handelsregister des Amtsgerichtes Stuttgart<br />". PHP_EOL;
            $body .= "Handelsregisternummer HRB 750816<br />";
            $body .= "<br />". PHP_EOL;
            $body .= "USt-IdNr.: DE297321596";
            $body .= PHP_EOL;
            
            $body .= "<br />". PHP_EOL;
            
            $body = utf8_decode($body);
            
            /* print_r($body);
            die(); */
            
            /*$body = "";
            $body .= 'Name: ' . $form->getValue('title') . " " . $form->getValue('name') . PHP_EOL;
            if ($form->getValue('company') != '') {
                $body .= 'Firma: ' . $form->getValue('company') . PHP_EOL;
            }
            $body .= 'Telefon: ' . $form->getValue('telephone') . PHP_EOL;
            $body .= 'E-Mail-Adresse: ' . $form->getValue('email');

            $body .= PHP_EOL . PHP_EOL;
            $body .= "Angaben zum Kopierer:" . PHP_EOL . PHP_EOL;
            $body .= "S/W oder Farbe: " . $form->getValue('question_type') . PHP_EOL;
            $body .= "Formate: " . join(', ', $form->getValue('question_format'));
            if ($form->getValue('question_formatmisc') != "") {
                $body .= ', ' . $form->getValue('question_formatmisc');
            }
            $body .= PHP_EOL;
            $body .= "Funktionen: " . join(', ', $form->getValue('question_function')) . PHP_EOL;
            $body .= "Papierfächer: " . $form->getValue('question_papertray') . PHP_EOL;
            $body .= "Anschlüsse: " . $form->getValue('question_connect') . PHP_EOL;
            $body .= "Anzahl Kopien / Monat: " . $form->getValue('question_volume') . PHP_EOL;
            $body .= "Tischgerät oder Standkopierer: " . $form->getValue('question_unit') . PHP_EOL . PHP_EOL;
            if ($form->getValue('question_budget')) {
                $body .= "Budget in Euro: " . $form->getValue('question_budgetamount') . PHP_EOL;
            } else {
                $body .= "Keine Budgetvorgaben" . PHP_EOL;
            }
            if ($form->getValue('question_note') != "") {
                $body .= PHP_EOL . "Sonstige Anmerkungen: " . PHP_EOL . $form->getValue('question_note') . PHP_EOL;
            }
            $body .= PHP_EOL;
            $body = utf8_decode($body);*/

            /*echo $body;
            die(); */

            $subject = "Kaufberatung Anfrage " . $form->getValue('title');
            if ($form->getValue('title') == 'Herr') {
                $subject .= "n";
            }
            $subject .= ' ' . $form->getValue('name');
            if ($form->getValue('company') != '') {
                $subject .= ', ' . $form->getValue('company');
            }
            $subject = utf8_decode($subject);
            $headers .= 'From: <kontakt@kopiererhaus.de>' . "\r\n";
           
            $toEmail = $form->getValue('email');
            $toName = $form->getValue('name');
            $mail = Mage::getModel('core/email');
            $mail = new Zend_Mail();
            $mail->addTo($toEmail);
            $mail->setSubject('Ihre Anfrage bei kopiererhaus.de');
            $mail->setBodyHtml($body);
			$mail-> setFrom('kontakt@kopiererhaus.de', 'Kopiererhaus.de | Kundenbetreuung');
			$mail->addCc('info@druckerboerse.com');			
			
            // $mail->setType('text'); // You can use 'html' or 'text'
			


            try {
               # $mail->send();
				$config = array(
				'ssl' => 'tls',
              'auth' => 'login',
              'username' => 'support-kopiererhaus@rz-huebner.com',
              'password' => 'FQe$rdiKf20G',
              'port' => 25
              );
                
				 $transport = new Zend_Mail_Transport_Smtp('smtp.office365.com', $config);
				
				
				$mail -> send($transport);
				
                Mage::getSingleton('core/session')->addSuccess('<div id="custom-agb"><p>Vielen Dank für Ihre Anfrage! Wir werden diese schnellstmöglich bearbeiten.</p></div>');
                $this->_redirect('kaufberatung-success');
            } catch (Exception $e) {
                //Mage::log('Exception: ' . $e . ' in ' . __CLASS__ . ' on ' . __LINE__);
                Mage::getSingleton('core/session')->addError('Unable to send request.');
                $this->_redirect('');
            }

            /*   $mail = new Zend_Mail();
              $mail -> setBodyText($body);

              $mail -> setFrom($fromEmail, $fromName);
              $mail -> addTo($toEmail, $toName);
              //$mail -> addBcc("werner.strauch@gmail.com", "Werner Strauch");
              $mail -> setSubject($subject);

              try {
              $config = array(
              'auth' => 'login',
              'username' => 'kontakt@kopiererhaus.de',
              'password' => 'kdb2sTPSGhHdCgittxAk',
              'port' => 25
              );
              $transport = new Zend_Mail_Transport_Smtp('smtp.kopiererhaus.de', $config);

              $file = Mage::getStoreConfig('dev/log/exception_file');
              Mage::log(
              'Kaufberatungsanfrage: ' . PHP_EOL . PHP_EOL . utf8_encode($body) ,
              Zend_Log::ERR,
              $file
              );

              $email -> send($transport);
              $formSuccess = true;
              } catch(Exception $ex) {
              //Mage::getSingleton('core/session') -> addError(Mage::helper('customer') -> __('Es ist ein unerwarteter Fehler aufgetreten. Ihre Anfrage konnte leider nicht gesendet werden.'));
              $file = Mage::getStoreConfig('dev/log/exception_file');
              Mage::log(
              'Anfrage konnte nicht gesendet werden: ' . $ex->getMessage() . PHP_EOL . PHP_EOL . utf8_encode($body) ,
              Zend_Log::ERR,
              $file
              );
              } */
        }

        //$block->assign("formSuccess", $formSuccess);
        //$block->assign("form", $form);

        //$form->setValues($data);
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
        ));

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
        ));

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
        ));

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
    /*protected function getForm() {

        $form = new Zend_Form();

        $questionType = new Zend_Form_Element_Radio('question_type');
        $questionType->addFilter('StripTags');
        $questionType->addFilter('StringTrim');
        $questionType->setRequired(false);
        $questionType->addMultiOptions(array(
            'Schwarz / Weiß' => 'Schwarz / Weiß',
            'Farbe und S/W' => 'Farbe und S/W',
        ));

        $questionVolume = new Zend_Form_Element_Radio('question_volume');
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

        $questionFunction = new Zend_Form_Element_MultiCheckbox('question_function');
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

        $questionPaper = new Zend_Form_Element_Radio('question_papertray');
        $questionPaper->addFilter('StripTags');
        $questionPaper->addFilter('StringTrim');
        $questionPaper->setRequired(false);
        $questionPaper->addMultiOptions(array(
            '2' => '2',
            '4' => '4',
            'mehr' => 'mehr',
            'weiß ich nicht' => 'weiß ich nicht',
        ));

        $questionConnect = new Zend_Form_Element_Radio('question_connect');
        $questionConnect->addFilter('StripTags');
        $questionConnect->addFilter('StringTrim');
        $questionConnect->setRequired(false);
        $questionConnect->addMultiOptions(array(
            'Netzwerk' => 'Netzwerk',
            'Lokal' => 'Lokal',
            'Standalone' => 'Standalone',
            'weiß ich nicht' => 'weiß ich nicht',
        ));

        $questionUnit = new Zend_Form_Element_Radio('question_unit');
        $questionUnit->addFilter('StripTags');
        $questionUnit->addFilter('StringTrim');
        $questionUnit->setRequired(false);
        $questionUnit->addMultiOptions(array(
            'Tischgerät' => 'Tischgerät',
            'Standkopierer' => 'Standkopierer',
            'weiß ich nicht' => 'weiß ich nicht',
        ));

        $questionFormat = new Zend_Form_Element_MultiCheckbox('question_format');
        $questionFormat->addFilter('StripTags');
        $questionFormat->addFilter('StringTrim');
        $questionFormat->setRequired(false);
        $questionFormat->addMultiOptions(array(
            'A4' => 'A4',
            'A3' => 'A3',
            'Etiketten' => 'Etiketten',
            'Broschüren' => 'Broschüren',
        ))->setValue('A4');

        $questionFormatMisc = new Zend_Form_Element_Text('question_formatmisc');
        $questionFormatMisc->addFilter('StripTags');
        $questionFormatMisc->addFilter('StringTrim');
        $questionFormatMisc->setRequired(false);

        $questionBudgetAmount = new Zend_Form_Element_Text('question_budgetamount');
        $questionBudgetAmount->addFilter('StripTags');
        $questionBudgetAmount->addFilter('StringTrim');
        $questionBudgetAmount->setRequired(false);
        //$questionBudgetAmount -> setAttrib('class', 'required-entry');

        $questionBudget = new Zend_Form_Element_Radio('question_budget');
        $questionBudget->addFilter('StripTags');
        $questionBudget->addFilter('StringTrim');
        $questionBudget->setRequired(false);
        $questionBudget->addMultiOptions(array(
            1 => 'Budget ca.',
            0 => 'Keine Budgetvorgaben',
        ))->setValue(1);

        $questionNote = new Zend_Form_Element_Textarea('question_note');
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

        $telephone = new Zend_Form_Element_Text('telephone');
        $telephone->addFilter('StripTags');
        $telephone->addFilter('StringTrim');
        $telephone->setRequired(true);
        $telephone->setAllowEmpty(false);
        $telephone->setAttrib('class', 'required-entry');

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
            $telephone,
            $email
        ));

        $form->setElementDecorators(array(
            'ViewHelper',
            'Label'
        ));

        return $form;
    }*/

}
