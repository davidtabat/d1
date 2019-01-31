<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/FireGento_Pdf/active')) {
                class AuIt_Pdf_Model_Invoice_Amasty_Pure extends FireGento_Pdf_Model_Invoice {}
            } else { class AuIt_Pdf_Model_Invoice_Amasty_Pure extends AuIt_Pdf_Model_Pdf_Base {} } ?>