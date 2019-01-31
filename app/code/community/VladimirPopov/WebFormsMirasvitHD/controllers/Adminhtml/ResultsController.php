<?php

class VladimirPopov_WebFormsMirasvitHD_Adminhtml_ResultsController extends Mage_Adminhtml_Controller_Action
{
    public function massConvertToTicketsAction()
    {
        $Ids = (array)$this->getRequest()->getParam('id');
        try {
            foreach ($Ids as $id) {
                Mage::helper('webformsmirasvithd')->convertToTicket($id);
            }

            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been converted to tickets.', count($Ids))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while updating records.'));
        }

        $this->_redirect('webforms_admin/adminhtml_results/', array('webform_id' => $this->getRequest()->getParam('webform_id')));
    }
}  
