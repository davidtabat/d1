$j.noConflict();

function calculateshippingblock()
{
    $j("input[id^='calculateshipping']").prop('disabled', true);
    $j("textarea[id^='calculateshipping']").prop('disabled', true);
    $j("select[id^='calculateshipping']").prop('disabled', true);
    $j("button[id^='calculateshipping']").prop('disabled', true);
    $j("[id*='cmsmart_license_skuproduct']").prop('disabled', false);
    $j("[id*='cmsmart_license_key']").prop('disabled', false);
    $j("[id*='cmsmart_license_domain']").prop('disabled', false);

    
}
function calculateshippingactive()
{
    $j("input[id^='calculateshipping']").prop('disabled', false);
    $j("textarea[id^='calculateshipping']").prop('disabled', false);
    $j("select[id^='calculateshipping']").prop('disabled', false);
    $j("button[id^='calculateshipping']").prop('disabled', false);
    $j("[id*='cmsmart_license_skuproduct']").prop('disabled', false);
    $j("[id*='cmsmart_license_key']").prop('disabled', false);
    $j("[id*='cmsmart_license_domain']").prop('disabled', false);

    
}

$j(window).load(function(){
    
    var root = location.protocol + '//' + location.host;
    //$j("[name='jform[params][domain]']").val(root);     
    var action_url = 'http://cmsmart.net/index.php?option=com_license&task=active';
    var product_sku = $j("[name='groups[cmsmart][fields][license_skuproduct][value]']").val();
    var license_key = $j("[name='groups[cmsmart][fields][license_key][value]']").val();
    var domain = window.location.host;
    
    calculateshippingblock();
    
    $j.ajax({
        type: 'POST',
        url: action_url,
        data: 'license[product_sku]='+product_sku+'&license[license_key]='+license_key+'&license[domain]='+domain,       
        dataType: 'json',
        beforeSend: function(){
                    $j('#ajax-loader').fadeIn("fast");
            },
        success: function(html){
                    $j('#ajax-loader').fadeOut("fast");
                    $j('#license-messages').text('');
                    
                    //$j('#license-messages').append(html.data);
                    jstr = '';
                    if(html.result==true)
                    {
                        jstr = '<span class="license-messages-success">'+html.data+'</span>';
                        
                        calculateshippingactive();
                    }
                    else
                    {
                        jstr = '<span class="license-messages-fail">'+html.data+'</span>';
                        
                        calculateshippingblock();                        
                    }
                    $j('#license-messages').text('');
                    $j('#license-messages').append(jstr);
            },
            error:function()
            {
                $j('#ajax-loader').fadeOut("fast");
                //jstr = '<span class="license-msfalse"><span class="icon-checkmark-circle fs32"></span><span class="license-msdes">No connect server cmsmart.net</span></span>';
                $j('#license-messages').text('');
                $j('#license-messages').append(jstr);
            }
        
        
    });       
        
  
   $j('.scalable').click(function(){
    
        var product_sku = $j("[name='jform[params][product_sku]']").val();
        var license_key = $j("[name='jform[params][license_key]']").val();
        var domain = window.location.host;
    
        $j.ajax({
            type: 'POST',
            url: action_url,
            data: 'license[product_sku]='+product_sku+'&license[license_key]='+license_key+'&license[domain]='+domain,
            dataType:'json',
            beforeSend: function(){
                        $j('#ajax-loader').fadeIn("fast");
                },
            success: function(html){
                    $j('#ajax-loader').fadeOut("fast");
                    $j('#license-messages').text('');
                    
                    //$j('#license-messages').append(html.data);
                    jstr = '';
                    if(html.result==true)
                    {
                        jstr = '<span class="license-messages-success">'+html.data+'</span>';
                        
                        calculateshippingactive();
                    }
                    else
                    {
                        jstr = '<span class="license-messages-fail">'+html.data+'</span>';
                        
                        calculateshippingblock();                        
                    }
                    $j('#license-messages').text('');
                    $j('#license-messages').append(jstr);
            },
            error:function()
            {
                $j('#ajax-loader').fadeOut("fast");
                //jstr = '<span class="license-msfalse"><span class="icon-checkmark-circle fs32"></span><span class="license-msdes">No connect server cmsmart.net</span></span>';
                $j('#license-messages').text('');
                $j('#license-messages').append(jstr);
            }
            
        });       
   
   });
   
   
    
   
});



