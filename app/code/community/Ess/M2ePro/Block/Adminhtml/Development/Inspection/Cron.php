<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Inspection_Cron
    extends Ess_M2ePro_Block_Adminhtml_Development_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentInspectionCron');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/inspection/cron.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $moduleConfig = Mage::helper('M2ePro/Module')->getConfig();

        $this->cronLastRunTime = 'N/A';
        $this->cronIsNotWorking = false;
        $this->cronCurrentRunner = ucfirst(Mage::helper('M2ePro/Module_Cron')->getRunner());
        $this->cronServiceAuthKey = $moduleConfig->getGroupValue('/cron/service/', 'auth_key');

        $baseDir = Mage::helper('M2ePro/Client')->getBaseDirectory();
        $this->cronPhp = 'php -q '.$baseDir.DIRECTORY_SEPARATOR.'cron.php -mdefault 1';

        $baseUrl = Mage::helper('M2ePro/Magento')->getBaseUrl();
        $this->cronGet = 'GET '.$baseUrl.'cron.php';

        $cronLastRunTime = Mage::helper('M2ePro/Module_Cron')->getLastRun();
        if ($cronLastRunTime !== null) {
            $this->cronLastRunTime = $cronLastRunTime;
            $this->cronIsNotWorking = Mage::helper('M2ePro/Module_Cron')->isLastRunMoreThan(12, true);
        }

        $cronServiceIps = array();

        for ($i = 1; $i < 100; $i++) {
            $serviceHostName = $moduleConfig->getGroupValue('/cron/service/', 'hostname_'.$i);

            if ($serviceHostName === null) {
                break;
            }

            $cronServiceIps[] = gethostbyname($serviceHostName);
        }

        $this->cronServiceIps = implode(', ', $cronServiceIps);

        $this->isMagentoCronDisabled = (bool)(int)$moduleConfig->getGroupValue('/cron/magento/', 'disabled');
        $this->isServiceCronDisabled = (bool)(int)$moduleConfig->getGroupValue('/cron/service/', 'disabled');

        return parent::_beforeToHtml();
    }

    //########################################

    public function isShownRecommendationsMessage()
    {
        if (!$this->getData('is_support_mode')) {
            return false;
        }

        if (Mage::helper('M2ePro/Module_Cron')->isRunnerMagento()) {
            return true;
        }

        if (Mage::helper('M2ePro/Module_Cron')->isRunnerService() && $this->cronIsNotWorking) {
            return true;
        }

        return false;
    }

    public function isShownServiceDescriptionMessage()
    {
        if (!$this->getData('is_support_mode')) {
            return false;
        }

        if (Mage::helper('M2ePro/Module_Cron')->isRunnerService() && !$this->cronIsNotWorking) {
            return true;
        }

        return false;
    }

    //########################################
}
