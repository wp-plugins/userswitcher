+function($){
    var is_admin = true, is_added = false,
    toggleSwitcher = function(){
        var target = $(this), form = target.parents('form').first(), box = form.find('select');
            is_open = box.width() > 20;
            box.css({width: is_open ? 0 : 120 });
    };
    
    var saveSettings = function(){
        var form = $(this).parents('form').first();
        form.submit();
    };
    
    var setUp = function(){
        var btn = $('.switcher-icon'),
            template = $('#switcher-template').html(), form, _class = 'switch-fixed';
      
        if( is_added ) return;
        
        if( btn.length == 0 ){
            $('body').prepend( template );
            is_admin = false;
        }
        else {
            btn.replaceWith( template );
            _class = 'switch-admin';
        }
        form = $('#switcher-form').addClass(_class);
    };
    
    $(document)
    .ready(setUp)
    .on('click.data-api', '#switcher-form .switch-icon', toggleSwitcher)
    .on('change.data-api', '#switcher-form select', saveSettings);
}(jQuery);