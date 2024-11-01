jQuery(function($){
    var wc_currency_front = {
        init: function () {
            $(document).on('click','li.wooexp-currency-child > a',{view:this},this.set_currency);
        },
        set_currency:function(e){
            e.preventDefault();
            var currency = $(this).attr('data-currency');
            wc_currency_front.update_currency('wooexp_currency',currency,90);
            window.location.reload(true);
        },
        get_currency:function(cname){
            var name = cname + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for(var i = 0; i <ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) === 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        },
        update_currency:function(cname,cvalue,exdays){
            document.cookie = cname + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
            var d = new Date();
            d.setTime(d.getTime() + (exdays*24*60*60*1000));
            var expires = "expires="+ d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }
    };
    wc_currency_front.init();
});