jQuery(function($){
    var wc_currency = {
        init: function () {
            $(document).on('click','.wooexp-currency-new',{view:this},this.add_currency);
            $(document).on('click','.wc_currency_add_main .add_currency',{view:this},this.add_new_currency);
            $(document).on('click','.remove-wooexp-currency',{view:this},this.remove_currency);
            $(document).on('click','.wooexp-currency-rates',{view:this},this.update_rates);
        },
        add_currency:function(){
            var count = $('table.wooexp-currency-list tbody tr').length;
            var html='';
            if(count<3){
                var options = wc_currency.get_currency_opts('');
                var currency_pos = wc_currency.get_currency_pos('');
                html='<div class="wc_currency_add_main">\
                <div class="currency-box"><h2>'+wooexp_curr.curr_head+'</h2></div>\
                <div class="currency-box-row"><label>'+wooexp_curr.curr_select+'</label><div class="currency-box-in"><select name="new-curr">'+options+'</select></div></div>\
                <div class="currency-box-row"><label>'+wooexp_curr.curr_pos+'</label><div class="currency-box-in"><select name="curr-pos">'+currency_pos+'</select></div></div>\
                <div class="currency-box-row"><label>'+wooexp_curr.thousand_sep+'</label><div class="currency-box-in"><input name="thousand_sep" type="text" value=","></div></div>\
                <div class="currency-box-row"><label>'+wooexp_curr.decimal_sep+'</label><div class="currency-box-in"><input name="decimal_sep" type="text" value="."></div></div>\
                <div class="currency-box-row"><label>'+wooexp_curr.decimal_num+'</label><div class="currency-box-in"><input name="decimal_num" type="number" step="1" value="2"></div></div>\
                <div class="currency-box-row"><label>'+wooexp_curr.exchange_rate+'</label><div class="currency-box-in"><input name="exchange_rate" type="text" value="1"></div></div>\
                <div class="currency-box-row"><label>'+wooexp_curr.auto_update+'</label><div class="currency-box-in"><input name="auto_update" type="checkbox"></div></div>\
                <div class="currency-box-row"><label></label><div class="currency-box-in"><input type="hidden" name="currency-box" value="'+wooexp_curr.ajax_nonce+'"><button type="button" name="add_currency" class="add_currency button-primary">'+wooexp_curr.add_currency+'</button></div></div>\
                </div>';
            }
            else
            {
                html='<div class="wc_currency_add_main">\
                <div class="currency-box"><h2>'+wooexp_curr.curr_head+'</h2></div>\
                <div class="currency-box-row"><a class="get-pro" href="https://wpexpertshub.com/plugins/multi-currency-pro-for-woocommerce/" target="_blank">'+wooexp_curr.pro_text+'</a></div>\
                </div>';
            }
            $.fancybox.open(html);
        },
        get_currency_opts(selected){
            var selected_opt = '';
            var options = '';
            var currencies = $('table.form-table-currency').attr('data-currencies');
            var currency = $('table.form-table-currency').attr('data-default');
            var obj = $.parseJSON(currencies);
            $.each(obj,function(i,val){
                if(currency!==i){
                    selected_opt = selected===i ? 'selected' : '';
                    options+='<option value="'+i+'" '+selected_opt+'>'+val+'</option>';
                }
            });
            return options;
        },
        get_currency_pos(selected){
            var selected_opt = '';
            var options = '';
            var currency_pos = $('table.form-table-currency').attr('data-pos');
            var obj = $.parseJSON(currency_pos);
            $.each(obj,function(i,val){
                selected_opt = selected===i ? 'selected' : '';
                options+='<option value="'+i+'" '+selected_opt+'>'+val+'</option>';
            });
            return options;
        },
        add_new_currency:function(){
            var currency = $(this).closest('.wc_currency_add_main').find("select[name=new-curr]").val();
            var currency_pos = $(this).closest('.wc_currency_add_main').find("select[name=curr-pos]").val();
            var thousand_sep = $(this).closest('.wc_currency_add_main').find("input[name=thousand_sep]").val();
            var decimal_sep = $(this).closest('.wc_currency_add_main').find("input[name=decimal_sep]").val();
            var decimal_num = $(this).closest('.wc_currency_add_main').find("input[name=decimal_num]").val();
            var exchange_rate = $(this).closest('.wc_currency_add_main').find("input[name=exchange_rate]").val();
            var auto_update = $(this).closest('.wc_currency_add_main').find("input[name=auto_update]:checked").length;
            var security = $(this).closest('.wc_currency_add_main').find("input[name=currency-box]").val();

            $.fancybox.close();
            $('table.wooexp-currency-list').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            $.ajax({
                url: wooexp_curr.ajax_url,
                type: "POST",
                dataType : 'json',
                cache	: false,
                data: {
                    "security":security,
                    "currency":currency,
                    'currency_pos' : currency_pos,
                    'thousand_sep' : thousand_sep,
                    'decimal_sep' : decimal_sep,
                    'decimal_num' : decimal_num,
                    'exchange_rate' : exchange_rate,
                    'auto_update' : auto_update,
                    'action': 'add_new_currency'
                },
                success:function(data){
                    $('table.wooexp-currency-list').unblock();
                   if(data.res){
                       var $response = $('<div />').html(data.html);
                       var content = $response.find('tbody').html();
                       var tbody = $(document).find('table.wooexp-currency-list tbody');
                       tbody.html(content);
                   }
                   if(data.notice!==''){
                       $(document).find('.wooexp-currency-notice').text(data.notice);
                   }
                }
            });
        },
        remove_currency:function(){
            var currency = $(this).attr('data-curr');
            var security = $(this).attr('data-security');
            $('table.wooexp-currency-list').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
            $.ajax({
                url: wooexp_curr.ajax_url,
                type: "POST",
                dataType : 'json',
                cache	: false,
                data: {
                    "currency":currency,
                    "security":security,
                    'action': 'remove_currency'
                },
                success:function(data){
                    $('table.wooexp-currency-list').unblock();
                    if(data.res){
                        var $response = $('<div />').html(data.html);
                        var content = $response.find('tbody').html();
                        var tbody = $(document).find('table.wooexp-currency-list tbody');
                        tbody.html(content);
                    }
                }
            });
        },
        update_rates:function(){
            var security = $(this).attr('data-security');
            $('table.wooexp-currency-list').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            $.ajax({
                url: wooexp_curr.ajax_url,
                type: "POST",
                dataType : 'json',
                cache	: false,
                data: {
                    "security":security,
                    'action': 'wooexp_currency_update_rates'
                },
                success:function(data){
                    $('table.wooexp-currency-list').unblock();
                    if(data.res){
                        var $response = $('<div />').html(data.html);
                        var content = $response.find('tbody').html();
                        var tbody = $(document).find('table.wooexp-currency-list tbody');
                        tbody.html(content);
                        $(document).find('.wooexp-currency-rate-time span').text(data.time);
                    }
                }
            });
        }
    };
    wc_currency.init();
});