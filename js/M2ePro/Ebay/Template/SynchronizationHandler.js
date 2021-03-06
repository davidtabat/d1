EbayTemplateSynchronizationHandler = Class.create();
EbayTemplateSynchronizationHandler.prototype = Object.extend(new CommonHandler(), {

    // ---------------------------------------

    initialize: function()
    {
        var self = this;
        Validation.add('validate-qty', M2ePro.translator.translate('Wrong value. Only integer numbers.'), function(value, el) {

            if (self.isElementHiddenFromPage(el)) {
                return true;
            }

            if (value.match(/[^\d]+/g) || value <= 0) {
                return false;
            }

            return true;
        });

        // ---------------------------------------
        Validation.add('M2ePro-validate-conditions-between', M2ePro.translator.translate('Must be greater than "Min".'), function(value, el) {

            var minValue = $(el.id.replace('_max','')).value;

            if (!el.up('tr').visible()) {
                return true;
            }

            return parseInt(value) > parseInt(minValue);
        });
        // ---------------------------------------

        // ---------------------------------------
        Validation.add('M2ePro-validate-stop-relist-conditions-product-status', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if ($('stop_status_disabled').value == 1 && $('relist_status_enabled').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-stock-availability', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if ($('stop_out_off_stock').value == 1 && $('relist_is_in_stock').value == 0) {
                return false;
            }

            return true;
        });

        Validation.add('M2ePro-validate-stop-relist-conditions-item-qty', M2ePro.translator.translate('Inconsistent Settings in Relist and Stop Rules.'), function(value, el) {

            if (EbayTemplateSynchronizationHandlerObj.isRelistModeDisabled()) {
                return true;
            }

            if (EbayTemplateSynchronizationHandlerObj.isStopModeDisabled()) {
                return true;
            }

            var stopMaxQty = 0,
                relistMinQty = 0;

            var qtyType = el.getAttribute('qty_type');

            switch (parseInt($('stop_qty_' + qtyType).value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_NONE'):
                    return true;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_LESS'):
                    stopMaxQty = parseInt($('stop_qty_' + qtyType + '_value').value);
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_BETWEEN'):
                    stopMaxQty = parseInt($('stop_qty_' + qtyType + '_value_max').value);
                    break;
            }

            switch (parseInt($('relist_qty_' + qtyType).value)) {

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_NONE'):
                    return false;
                    break;

                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE'):
                case M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN'):
                    relistMinQty = parseInt($('relist_qty_' + qtyType + '_value').value);
                    break;
            }

            if (relistMinQty <= stopMaxQty) {
                return false;
            }

            return true;
        });
        // ---------------------------------------
    },

    // ---------------------------------------

    isRelistModeDisabled: function()
    {
        return $('relist_mode').value == 0;
    },

    isStopModeDisabled: function()
    {
        return $('stop_mode').value == 0;
    },

    // ---------------------------------------

    getNavigationTabName: function(element)
    {
        return $('ebay_template_synchronization_edit_form_navigation_bar_' + element.id.split('_').shift());
    },

    // ---------------------------------------

    setVirtualTabsAsInactive: function()
    {
        $$('#ebay_template_synchronization_edit_form_container .form_content').invoke('hide');
        $$('#ebay_template_synchronization_edit_form_container .navigation_bar').invoke('removeClassName','active');
    },

    setVirtualTabAsActive: function()
    {
        EbayTemplateSynchronizationHandlerObj.setVirtualTabsAsInactive();

        $(this.id.replace('navigation_bar','content')).show();
        this.addClassName('active');
    },

    setVirtualTabAsChanged: function()
    {
        var tab = EbayTemplateSynchronizationHandlerObj.getNavigationTabName(this);
        tab.addClassName('changed');
    },

    checkVirtualTabValidation: function()
    {
        var failedItems = $$('#ebay_template_synchronization_edit_form_container .validation-failed');

        $$('#ebay_template_synchronization_edit_form_container .navigation_bar').invoke('removeClassName','error');

        failedItems.each(function(el) {
            var tab = EbayTemplateSynchronizationHandlerObj.getNavigationTabName(el);
            tab.addClassName('error');
        });

        if (failedItems.length > 0) {
            EbayTemplateSynchronizationHandlerObj.setVirtualTabsAsInactive();

            var tab = EbayTemplateSynchronizationHandlerObj.getNavigationTabName(failedItems.shift());
            $(tab.id.replace('navigation_bar','content')).show();
            tab.addClassName('active');
        }
    },

    // ---------------------------------------

    listMode_change: function()
    {
        $('magento_block_ebay_template_synchronization_form_data_list_rules').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_MODE_YES')) {
            $('magento_block_ebay_template_synchronization_form_data_list_rules').show();
        }
    },

    listQty_change: function()
    {
        var qtyType = this.getAttribute('qty_type');

        var valueContainer    = $('list_qty_' + qtyType + '_value_container'),
            valueMaxContainer = $('list_qty_' + qtyType + '_value_max_container'),
            itemMin           = $('list_qty_' + qtyType + '_item_min'),
            item              = $('list_qty_' + qtyType + '_item');

        valueContainer.hide();
        valueMaxContainer.hide();
        itemMin.hide();
        item.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_LESS') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_MORE')) {
            item.show();
            valueContainer.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_QTY_BETWEEN')) {
            itemMin.show();
            valueContainer.show();
            valueMaxContainer.show();
        }
    },

    // ---------------------------------------

    reviseQty_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_UPDATE_QTY_YES')) {
            $('revise_update_qty_max_applied_value_mode_tr').show();
            $('revise_update_qty_max_applied_value_line_tr').show();
            $('revise_update_qty_max_applied_value_mode').simulate('change');
        } else {
            $('revise_update_qty_max_applied_value_mode_tr').hide();
            $('revise_update_qty_max_applied_value_line_tr').hide();
            $('revise_update_qty_max_applied_value_tr').hide();
            $('revise_update_qty_max_applied_value_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_OFF');
        }
    },

    reviseQtyMaxAppliedValueMode_change: function(event)
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        $('revise_update_qty_max_applied_value_tr').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_ON')) {
            $('revise_update_qty_max_applied_value_tr').show();
        } else if (!event.cancelable) {
            self.openReviseMaxAppliedQtyDisableConfirmationPopUp();
        }
    },

    openReviseMaxAppliedQtyDisableConfirmationPopUp: function()
    {
        Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Are you sure?',
            width: 600,
            height: 400,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                $('revise_update_qty_max_applied_value_mode').selectedIndex = 1;
                $('revise_update_qty_max_applied_value_mode').simulate('change');
            }
        });

        $('modal_dialog_message').update($('revise_qty_max_applied_value_confirmation_popup_template').innerHTML);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    reviseQtyMaxAppliedValueDisableConfirm: function()
    {
        Windows.getFocusedWindow().close();

        $('revise_update_qty_max_applied_value_mode').selectedIndex = 0;
        $('revise_update_qty_max_applied_value_mode').simulate('change');
    },

    // ---------------------------------------

    revisePrice_change: function()
    {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_UPDATE_PRICE_YES')) {
            $('revise_update_price_max_allowed_deviation_mode_tr').show();
            $('revise_update_price_max_allowed_deviation_tr').show();
            $('revise_update_price_line').show();
            $('revise_update_price_max_allowed_deviation_mode').simulate('change');
        } else {
            $('revise_update_price_max_allowed_deviation_mode_tr').hide();
            $('revise_update_price_max_allowed_deviation_tr').hide();
            $('revise_update_price_line').hide();
            $('revise_update_price_max_allowed_deviation_mode').value = M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF');
        }
    },

    revisePriceMaxAllowedDeviationMode_change: function(event)
    {
        var self = EbayTemplateSynchronizationHandlerObj;

        $('revise_update_price_max_allowed_deviation_tr').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON')) {
            $('revise_update_price_max_allowed_deviation_tr').show();
        } else if (!event.cancelable) {
            self.openReviseMaxAllowedDeviationPriceDisableConfirmationPopUp();
        }
    },

    openReviseMaxAllowedDeviationPriceDisableConfirmationPopUp: function()
    {
        Dialog.info(null, {
            draggable: true,
            resizable: true,
            closable: true,
            className: "magento",
            windowClassName: "popup-window",
            title: 'Are you sure?',
            width: 600,
            height: 400,
            zIndex: 100,
            hideEffect: Element.hide,
            showEffect: Element.show,
            onClose: function() {
                $('revise_update_price_max_allowed_deviation_mode').selectedIndex = 1;
                $('revise_update_price_max_allowed_deviation_mode').simulate('change');
            }
        });

        $('modal_dialog_message').update($('revise_price_max_max_allowed_deviation_confirmation_popup_template').innerHTML);

        setTimeout(function() {
            Windows.getFocusedWindow().content.style.height = '';
            Windows.getFocusedWindow().content.style.maxHeight = '630px';
        }, 50);
    },

    revisePriceMaxAllowedDeviationDisableConfirm: function()
    {
        Windows.getFocusedWindow().close();

        $('revise_update_price_max_allowed_deviation_mode').selectedIndex = 0;
        $('revise_update_price_max_allowed_deviation_mode').simulate('change');
    },

    // ---------------------------------------

    relistMode_change: function()
    {
        $('relist_filter_user_lock_tr_container').hide();
        $('magento_block_ebay_template_synchronization_form_data_relist_rules').hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_MODE_YES')) {
            $('relist_filter_user_lock_tr_container').show();
            $('magento_block_ebay_template_synchronization_form_data_relist_rules').show();
        }
    },

    relistQty_change: function()
    {
        var qtyType = this.getAttribute('qty_type');

        var valueContainer    = $('relist_qty_' + qtyType + '_value_container'),
            valueMaxContainer = $('relist_qty_' + qtyType + '_value_max_container'),
            itemMin           = $('relist_qty_' + qtyType + '_item_min'),
            item              = $('relist_qty_' + qtyType + '_item');

        valueContainer.hide();
        valueMaxContainer.hide();
        itemMin.hide();
        item.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_LESS') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE')) {
            item.show();
            valueContainer.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN')) {
            itemMin.show();
            valueContainer.show();
            valueMaxContainer.show();
        }
    },

    // ---------------------------------------

    stopQty_change: function()
    {
        var qtyType = this.getAttribute('qty_type');

        var valueContainer    = $('stop_qty_' + qtyType + '_value_container'),
            valueMaxContainer = $('stop_qty_' + qtyType + '_value_max_container'),
            itemMin           = $('stop_qty_' + qtyType + '_item_min'),
            item              = $('stop_qty_' + qtyType + '_item');

        valueContainer.hide();
        valueMaxContainer.hide();
        itemMin.hide();
        item.hide();

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_LESS') ||
            this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_MORE')) {
            item.show();
            valueContainer.show();
        }

        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_QTY_BETWEEN')) {
            itemMin.show();
            valueContainer.show();
            valueMaxContainer.show();
        }
    },

    // ---------------------------------------

    stopMode_change: function () {
        if (this.value == M2ePro.php.constant('Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_MODE_YES')) {
            $('magento_block_ebay_template_synchronization_form_data_stop_rules').show();
        } else {
            $('magento_block_ebay_template_synchronization_form_data_stop_rules').hide();
        }
    }

    // ---------------------------------------
});