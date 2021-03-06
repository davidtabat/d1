<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Help Desk MX
 * @version   1.2.4
 * @build     2266
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Helpdesk_Helper_Checkenv extends Mage_Core_Helper_Abstract
{
    /**
     * @param Mirasvit_Helpdesk_Model_Gateway $gateway
     *
     * @return string
     */
    public function checkGateway($gateway)
    {
        $result = array();
        $ports = array($gateway->getHost() => $gateway->getPort());
        foreach ($ports as $host => $port) {
            $connection = @fsockopen($host, $port);
            if (is_resource($connection)) {
                $result[] = $host.':'.$port.' '.'('.getservbyport($port, 'tcp').') is open.';
                fclose($connection);
            } else {
                $result[] = $host.':'.$port.' is closed.';
            }
        }

        return implode('<br>', $result);
    }
}
