function openPaidPopup()
{
    
    flagDialog = Dialog.info($('paypayment').innerHTML,{
        draggable:true,
        resizable:false,
        closable:true,
        className:"magento",
        windowClassName:"popup-window",
        title:'Flag For Order # (Column: )',
        width:400,
        //height:270,
        zIndex:1000,
        recenterAuto:false,
        hideEffect:Element.hide,
        showEffect:Element.show,
        id:'dialog-',
    //    onClose: this.closeDialogWindow.bind(this)
    });
}

function setPaidAmount(url,orderId )
{
    if (orderId)
    {
        // setting order flag
       var  amount = $('paymenpaid'+orderId).value;
        flagDialog.close();
        
        // saving flag id to server
        postData = 'form_key=' + FORM_KEY + '&order_id=' + orderId + '&amount=' + amount ;
        new Ajax.Request(url, 
        {
            method: 'post',
            postBody : postData,
            onSuccess: function(transport) 
            {
                
            }
        });
    } else 
    {
       
    }
}
