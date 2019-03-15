<?php
/**
 * This file is part of Hackathon_IndexerStats for Magento.
 *
 * @license Open Software License (OSL 3.0)
 * @author Fabian Schmengler <fs@integer-net.de> <@fschmengler>
 * @category Hackathon
 * @package Hackathon_IndexerStats
 * @copyright Copyright (c) 2014 Magento Hackathon (http://github.com/magento-hackathon)
 */

/**
 * Observer Model
 * @package Hackathon_IndexerStats
 */
class Hackathon_IndexerStats_Model_Observer extends Mage_Core_Model_Abstract
{

    const AFTER_REINDEX_PROCESS_EVENT_PREFIX = 'after_reindex_process_';

// Magento Hackathon Tag NEW_CONST

// Magento Hackathon Tag NEW_VAR

    /**
     * @see event adminhtml_block_html_before
     * @return 
     */
    public function addIndexStatusColumn(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Block_Template $block */
        $block = $observer->getBlock();
        if ($block instanceof Mage_Index_Block_Adminhtml_Process_Grid) {
            $this->_addIndexStatusColumnTo($block);
            $this->_changeActionColumnToAjax($block);
        }
    }
    protected function _addIndexStatusColumnTo(Mage_Index_Block_Adminhtml_Process_Grid $grid)
    {
        $grid->addColumn('status_extended', array(
            'header'    => Mage::helper('hackathon_indexerstats')->__('Status (extended)'),
            'width'     => '200',
            'align'     => 'left',
            'index'     => 'status_extended',
            'renderer'  => 'hackathon_indexerstats/adminhtml_index_status',
        ));
    }
    protected function _changeActionColumnToAjax(Mage_Index_Block_Adminhtml_Process_Grid $grid)
    {
        $grid->getColumn('action')->setActions(
            array(
                array(
                    'caption' => Mage::helper('index')->__('Reindex Data'),
                    'url' => array('base' => '*/*/reindexProcessAjax'),
                    'onclick' => "new IndexerStats.AjaxRequest(this); return false;",
                    'field' => 'process'
                )
            ));
        /*
         * We use the IndexerStats.AjaxRequest class only for response here, normal Ajax.Request
         * is hard coded in grid.js as only non-submitting action
         * 
         * eval'd "complete" value gets used as callback in JavaScript.
         */
        $grid->getMassactionBlock()->setUseAjax(true)
            ->getItem('reindex')
            ->setUrl($grid->getUrl('*/*/massReindexAjax'))
            ->setComplete('(rq = new IndexerStats.AjaxRequest()).onMassComplete.bind(rq)');
    }

    /**
     * @see event after_reindex_process_*
     * @param Varien_Event_Observer $observer
     */
    public function saveHistory(Varien_Event_Observer $observer)
    {
        $indexerCode = substr($observer->getEvent()->getName(), strlen(self::AFTER_REINDEX_PROCESS_EVENT_PREFIX));
        /** @var Mage_Index_Model_Process $process */
        $process = Mage::getModel('index/process')->load($indexerCode, 'indexer_code');
        if ($process->getStatus() === Mage_Index_Model_Process::STATUS_PENDING) {
            /** @var Hackathon_IndexerStats_Model_History $processHistory */
            $processHistory = Mage::getModel('hackathon_indexerstats/history');
            $processHistory->setDataFromProcess($process)->save();
        }
    }
// Magento Hackathon Tag NEW_METHOD

}