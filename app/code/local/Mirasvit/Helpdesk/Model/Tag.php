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



/**
 * @method Mirasvit_Helpdesk_Model_Resource_Tag_Collection|Mirasvit_Helpdesk_Model_Tag[] getCollection()
 * @method Mirasvit_Helpdesk_Model_Tag load(int $id)
 * @method bool getIsMassDelete()
 * @method Mirasvit_Helpdesk_Model_Tag setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Mirasvit_Helpdesk_Model_Tag setIsMassStatus(bool $flag)
 * @method Mirasvit_Helpdesk_Model_Resource_Tag getResource()
 * @method string getName()
 * @method Mirasvit_Helpdesk_Model_Tag setName(string $param)
 */
class Mirasvit_Helpdesk_Model_Tag extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdesk/tag');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /************************/
}
