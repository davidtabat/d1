<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_NotListed
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    public function isAllowed()
    {
        $listingProduct = $this->_input->getListingProduct();

        if (!$listingProduct->isListable() || !$listingProduct->isNotListed()) {
            return false;
        }

        return true;
    }

    //########################################

    public function process(array $params = array())
    {
        if (!$this->isMeetListRequirements()) {
            if ($this->_input->getScheduledAction() && !$this->_input->getScheduledAction()->isForce()) {
                $this->getScheduledActionManager()->deleteAction($this->_input->getScheduledAction());
            }

            return;
        }

        if ($this->_input->getScheduledAction() && $this->_input->getScheduledAction()->isActionTypeList()) {
            return;
        }

        $scheduledAction = $this->_input->getScheduledAction();
        if ($scheduledAction === null) {
            $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
        }

        $scheduledAction->addData(
            array(
                'listing_product_id' => $this->_input->getListingProduct()->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
                'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
                'additional_data'    => Mage::helper('M2ePro')->jsonEncode(array('params' => $params)),
            )
        );

        if ($scheduledAction->getId()) {
            $this->getScheduledActionManager()->updateAction($scheduledAction);
        } else {
            $this->getScheduledActionManager()->addAction($scheduledAction);
        }
    }

    //########################################

    public function isMeetListRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isListMode()) {
            return false;
        }

        $additionalData = $listingProduct->getAdditionalData();

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($ebaySynchronizationTemplate->isListStatusEnabled()) {
            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                // M2ePro_TRANSLATIONS
                // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                // Status of Magento Product is Disabled (%date%) though in Synchronization Rules “Product Status”
                // is set to Enabled.
                $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                     Status of Magento Product is Disabled (%date%) though in Synchronization Rules “Product Status”
                     is set to Enabled.',
                    array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($ebayListingProduct->isVariationsReady()) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                    // Status of Magento Product Variation is Disabled (%date%) though in Synchronization Rules
                    // “Product Status“ is set to Enabled.
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Status of Magento Product Variation is Disabled (%date%) though in Synchronization Rules
                         “Product Status“ is set to Enabled.',
                        array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isListIsInStock()) {
            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                // M2ePro_TRANSLATIONS
                // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                // Stock Availability of Magento Product is Out of Stock though in Synchronization Rules
                // “Stock Availability” is set to In Stock.
                $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                    'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                     Stock Availability of Magento Product is Out of Stock though in
                     Synchronization Rules “Stock Availability” is set to In Stock.',
                    array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } else if ($ebayListingProduct->isVariationsReady()) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                    // Stock Availability of Magento Product Variation is Out of Stock though in Synchronization Rules
                    // “Stock Availability” is set to In Stock.
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Stock Availability of Magento Product Variation is Out of Stock though
                         in Synchronization Rules “Stock Availability” is set to In Stock.',
                        array('date' => Mage::helper('M2ePro')->getCurrentGmtDate())
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isListWhenQtyMagentoHasValue()) {
            $result = false;
            $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getListWhenQtyMagentoHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                    // Quantity of Magento Product is %product_qty% though in Synchronization Rules
                    // “Magento Quantity“ is set to less then  %template_min_qty%.
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity“ is set to less then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                    // Quantity of Magento Product is %product_qty% though in Synchronization Rules
                    // “Magento Quantity” is set to more then  %template_min_qty%.
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity” is set to more then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                    // Quantity of Magento Product is %product_qty% though in Synchronization Rules
                    // “Magento Quantity” is set between  %template_min_qty% and %template_max_qty%.
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Magento Quantity” is set between  %template_min_qty% and %template_max_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!template_max_qty' => $maxQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($ebaySynchronizationTemplate->isListWhenQtyCalculatedHasValue()) {
            $result = false;
            $productQty = (int)$ebayListingProduct->getQty();

            $typeQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getListWhenQtyCalculatedHasValueMax();

            $note = '';

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS) {
                if ($productQty <= $minQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                    // Quantity of Magento Product is %product_qty% though in Synchronization Rules
                    // “Calculated Quantity” is set to less then %template_min_qty%.
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set to less then %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE) {
                if ($productQty >= $minQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                    // Quantity of Magento Product is %product_qty% though in Synchronization Rules
                    // “Calculated Quantity” is set to more then  %template_min_qty%.
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set to more then  %template_min_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN) {
                if ($productQty >= $minQty && $productQty <= $maxQty) {
                    $result = true;
                } else {
                    // M2ePro_TRANSLATIONS
                    // Product was not automatically Listed according to the List Rules in Synchronization Policy.
                    // Quantity of Magento Product is %product_qty% though in Synchronization Rules
                    // “Calculated Quantity” is set between  %template_min_qty% and %template_max_qty%.
                    $note = Mage::helper('M2ePro/Module_Log')->encodeDescription(
                        'Product was not automatically Listed according to the List Rules in Synchronization Policy.
                         Quantity of Magento Product is %product_qty% though in Synchronization Rules
                         “Calculated Quantity” is set between  %template_min_qty% and %template_max_qty%.',
                        array(
                            '!template_min_qty' => $minQty,
                            '!template_max_qty' => $maxQty,
                            '!product_qty' => $productQty,
                            '!date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                        )
                    );
                }
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        return true;
    }

    //########################################
}
