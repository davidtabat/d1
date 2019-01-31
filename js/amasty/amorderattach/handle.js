if ("undefined" == typeof(FORM_KEY)) {
    FORM_KEY = '';
}

function attachEdit(field) {
    $('viewblock_' + field).style.display = 'none';
    $('editblock_' + field).style.display = 'block';
}

function attachCancel(field) {
    $('editblock_' + field).style.display = 'none';
    $('viewblock_' + field).style.display = 'block';
}

function attachSave(field, type) {
    attachShowProcess();

    orderId = document.getElementsByName('order_id')[0].value;
    value = $('value_' + field).value;

    postData = 'form_key=' + FORM_KEY + '&order_id=' + orderId + '&field=' + field + '&value=' + value + '&type=' + type;

    new Ajax.Request(attachSaveUrl, {
        method: 'post',
        postBody: postData,
        onSuccess: function (transport) {
            $('field_' + field).innerHTML = transport.responseText;
            if ('date' == type) {
                // should re-setup calendar as it adds observers
                Calendar.setup({
                    inputField: 'value_' + field,
                    ifFormat: "%Y/%m/%d",
                    showsTime: false,
                    button: 'value_' + field + '_trig',
                    align: "Bl",
                    singleClick: true
                });
            }
        },
        onComplete: function () {
            attachHideProcess();
        }
    });
}

function attachSaveProd(field, type) {
    attachShowProcess();

    orderId = document.getElementsByName('order_id')[0].value;
    value = $('value_' + field).value;

    postData = 'form_key=' + FORM_KEY + '&order_id=' + orderId + '&product_form=1' + '&field=' + field + '&value=' + value + '&type=' + type;

    new Ajax.Request(attachSaveUrl, {
        method: 'post',
        postBody: postData,
        onSuccess: function (transport) {
            $('field_' + field).innerHTML = transport.responseText;
            if ('date' == type) {
                // should re-setup calendar as it adds observers
                Calendar.setup({
                    inputField: 'value_' + field,
                    ifFormat: "%Y/%m/%d",
                    showsTime: false,
                    button: 'value_' + field + '_trig',
                    align: "Bl",
                    singleClick: true
                });
            }
        },
        onComplete: function () {
            attachHideProcess();
        }
    });
}

function attachUpload(field) {
    attachShowProcess();
    $('upload_form_' + field).target = 'upload_target_' + field;
    $('upload_target_' + field).observe('load', function () {

        orderId = document.getElementsByName('order_id')[0].value;
        postData = 'form_key=' + FORM_KEY + '&order_id=' + orderId + '&field=' + field;

        new Ajax.Request(attachReloadUrl, {
            method: 'post',
            postBody: postData,
            onSuccess: function (transport) {
                $('field_' + field).innerHTML = transport.responseText;
            },
            onComplete: function () {
                attachHideProcess();
            }
        });

    });
    $('upload_form_' + field).submit();
}


function attachUploadProd(field) {
    attachShowProcess();
    $('upload_form_' + field).target = 'upload_target_' + field;
    $('upload_target_' + field).observe('load', function () {
        orderId = document.getElementsByName('order_id')[0].value;
        postData = 'form_key=' + FORM_KEY + '&order_id=' + orderId + '&product_form=1' + '&field=' + field;
        new Ajax.Request(attachReloadUrl, {
            method: 'post',
            postBody: postData,
            onSuccess: function (transport) {
                $('field_' + field).innerHTML = transport.responseText;
            },
            onComplete: function () {
                attachHideProcess();
            }
        });

    });
    $('upload_form_' + field).submit();
}

function attachDeleteFile(field, file, type) {
    attachShowProcess();
    orderId = document.getElementsByName('order_id')[0].value;
    postData = 'form_key=' + FORM_KEY + '&order_id=' + orderId + '&field=' + field + '&type=' + type + '&file=' + file;

    new Ajax.Request(attachDeleteUrl, {
        method: 'post',
        postBody: postData,
        onSuccess: function (transport) {
            $('field_' + field).innerHTML = transport.responseText;
        },
        onComplete: function () {
            attachHideProcess();
        }
    });
}

function attachDeleteFileProd(field, file, type) {
    attachShowProcess();
    orderId = document.getElementsByName('order_id')[0].value;
    postData = 'form_key=' + FORM_KEY + '&order_id=' + orderId + '&product_form=1' + '&field=' + field + '&type=' + type + '&file=' + file;

    new Ajax.Request(attachDeleteUrl, {
        method: 'post',
        postBody: postData,
        onSuccess: function (transport) {
            $('field_' + field).innerHTML = transport.responseText;
        },
        onComplete: function () {
            attachHideProcess();
        }
    });
}

function attachShowProcess() {
    if ($('amattach-block')) {
        $('amattach-block').setOpacity(0.2);
    }
    if ($('my-orders-table')) {
        $('my-orders-table').setOpacity(0.2);
    }
    if ($('amattach-pleasewait')) {
        $('amattach-pleasewait').show();
    }
}

function attachHideProcess() {
    if ($('amattach-block')) {
        $('amattach-block').setOpacity(1);
    }
    if ($('my-orders-table')) {
        $('my-orders-table').setOpacity(1);
    }
    if ($('amattach-pleasewait')) {
        $('amattach-pleasewait').hide();
    }
}