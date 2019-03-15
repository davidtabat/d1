<?php
/**
 * This file is part of the Itabs_Invoice extension.
 *
 * PHP version 5
 *
 * @category  Itabs
 * @package   Itabs_Invoice
 * @author    ITABS GmbH <info@itabs.de>
 * @copyright 2013-2015 ITABS GmbH (http://www.itabs.de)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version   1.4.0
 * @link      https://github.com/itabs/Itabs_Invoice
 */

/**
 * Observer for payment method availability checks
 */
class Itabs_Invoice_Model_Observer
{
    /**
     * Checks if Invoice is allowed for specific customer groups and if a
     * registered customer has the required minimum amount of orders to be
     * allowed to order via Invoice.
     *
     * Event: <payment_method_is_active>
     *
     * @param  Varien_Event_Observer $observer Observer
     * @return void
     */
    public function paymentMethodIsActive($observer)
    {
        /* @var $methodInstance Mage_Payment_Model_Method_Abstract */
        $methodInstance = $observer->getEvent()->getMethodInstance();

        // Check if method is Invoice
        if ($methodInstance->getCode() != 'invoice') {
            return;
        }

        // Check if payment method is active
        if (!Mage::getStoreConfigFlag('payment/invoice/active')) {
            return;
        }

        /* @var $validationModel Itabs_Invoice_Model_Validation */
        $validationModel = Mage::getModel('invoice/validation');
        $observer->getEvent()->getResult()->isAvailable = $validationModel->isValid();
    }
}
