<?php /* added automatically by conflict fixing tool */ if (Mage::getConfig()->getNode('modules/IWD_All/active')) {
                class IWD_OrderManager_Model_Payment_Authorizenet_Amasty_Pure extends IWD_All_Model_Paygate_Authorizenet {}
            } else { class IWD_OrderManager_Model_Payment_Authorizenet_Amasty_Pure extends IWD_OrderManager_Model_Payment_Authorizenet_Rewrite {} } ?>