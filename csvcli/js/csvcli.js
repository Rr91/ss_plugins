if (typeof($) != 'undefined') {
    $.extend($.importexport = $.importexport || {}, {
        csvcliUploadProgress: function(e, data){
            this.csv_productUploadProgress(e, data);
        },

        csvcliUploadDone: function (e, data) {
            if ( data && data.result ){
                this.csv_productUploadDone(e, data);
                $('#csvcli').addClass('show-submit');
            }
        },

        csvcliUploadFail: function(e, data){
            this.csv_productUploadFail(e, data);
        },

        csvcliBlur: function(plugin, current_plugin){
            this.importexportBlur(plugin, current_plugin);
        }
    });
}


var loader = '<i class="icon16 loading"></i>';

 function initCsv(is_new){

     is_new = is_new || false;

     setTimeout(function(){

        $.importexport.csvcliUploadDone({}, csvcli_view_data);

         if ( csvcli_csv_map ){
             var item, wrp = $('#csvcli .s-csv .s-csv-controls');
             for ( var i in csvcli_csv_map ) {
                 item = csvcli_csv_map[i];
                 if ( item ){
                     $(wrp).find('select[name="csv_map[' + i + ']"] option[value="' + item + '"]').prop('selected', true);
                 }
             }
         }
     }, 300);

     if ( csvcli_settings ){
         var s, element, t;
         for ( var i in csvcli_settings ){
             s = csvcli_settings[i];

             if ( i !== 'csv_map' ) {
                 element = $(document).find('[name="' + i + '"]');
                 t = $(element).prop('tagName');

                 if (t === 'SELECT') {
                     $(element).find('option[value="' + s + '"]').prop('selected', true);
                 } else if (t === 'INPUT') {
                     if ( $(element).attr('type') === 'radio' ){
                         $(document).find('[name="' + i + '"][value="' + s + '"]').prop('checked', true);
                     } else if ($(element).attr('type') === 'checkbox') {
                         $(element).prop('checked', !!s);
                     } else {
                         $(element).val(s);
                     }
                 }
             }
         }

         if ( csvcli_settings.file ){
             $.importexport.csv_productInit();
             $.importexport.profiles.set('csv:product:import', []);
         }
     }
 }

 function profile(profile_id, in_background, is_new){
        var profile_content = $(document).find("#csvcli .s-csv-settings"),
            form            = profile_content.find('form'),
            tabs            = $(document).find('#s-csvcli-profile'),
            in_background   = !!in_background;

        is_new = is_new || false;

        tabs.find('.selected').removeClass('selected');

        if ( !in_background ){
            form.css('opacity', 0);
            profile_content.prepend(loader);
        }

        var tab_selector = '#' + (profile_id ? 'profile' + profile_id : 'csvcli-default-profile');
        $(document).find(tab_selector).addClass('selected');

        $.ajax({
            type: 'POST',
            url:  '?plugin=csvcli&module=backend&action=setup',
            data: { profile: profile_id },
            success: function(r){
                $('#csvcli .s-csv-settings .icon16.loading').remove();

                if ( r ){
                    var d = $('<div/>').html(r);
                    $(document).find('#csvcli .s-csv-settings').html($(d).find('#csvcli .s-csv-settings').html());
                    initCsv(is_new);
                }
            }
        });
    }

$(document).on('click', '#csvcli .csvcli-save', function(e){
    e.stopImmediatePropagation();

    var form   = $('#csvcli form'),
        status = $('#csvcli-status');

    status.html('<i class="icon16 loading"></i>');

    var profile_n_inp = $('#csvcli form input[name=profile_name]'),
        profile_name  = profile_n_inp.val();

    if ( !profile_name ){
        profile_name = 'Без названия';
        profile_n_inp.val(profile_name);
    }

    $.ajax({
        type: 'POST',
        data: form.serialize() + '&encoding=' + form.find('select[name="encoding"]').val(),
        url: '?plugin=csvcli&module=save',
        success: function(){
            status.html('<i class="icon16 yes"></i>');
        }
    }).always(function(){
        setTimeout(function(){
            status.html('');
        },1500);

        $('#s-csvcli-profile li.selected a').text(profile_name);
    });
});

$(document).on('click', '#new-profile', function(e){
    e.preventDefault();
    e.stopImmediatePropagation();

    var self = $(this);

    $.ajax({
        type:    'POST',
        url:     '?plugin=csvcli&module=profile&action=add',
        success: function(r){
            if ( r.status == 'ok' ){
                var profile_id   = r.data.profile_id,
                    profile_name = r.data.name;

                if ( profile_id ){
                    var tab_nav = '<li data-id="' + profile_id + '" class="selected" id="profile' + profile_id + '">' +
                        '<a href="#/csvcli:' + profile_id + '/">' + profile_name + '</a>' +
                        '</li>';

                    self.before(tab_nav);
                    profile(profile_id, false, true);
                }
            }
        }
    });
});

$(document).on('click', '#csvcli .delete-link-wrapper > a', function(e){
    e.preventDefault();
    e.stopImmediatePropagation();

    var $this    = $(this),
        current = $this.parent().parent().find('li.selected');

    if ( confirm($this.data('confirm-text')) ){
        $.ajax({
            type: 'POST',
            url: '?plugin=csvcli&module=profile&action=delete',
            data: {profile_id: $(current).data('id')},
            success: function(r){
                if ( r.status === 'ok' ){

                    var next = current.next(':not(.no-tab)');

                    if ( !$(next).length ){
                        next = current.prev();
                    }

                    current.fadeOut(150, function(){
                        $(this).remove();
                    });

                    profile($(next).data('id') || 0);

                } else {
                    alert('Произошла ошибка');
                }
            }
        });
    }

});

$(document).on('click', '#csvcli .csvcli-profiles li:not(.add-profile)', function(e){
    e.preventDefault();
    e.stopImmediatePropagation();

    var self = $(this);
    if ( self.hasClass('selected') ){
        return false;
    }
    var profile_id = self.data('id');
    profile(profile_id);
});

var f = '<input type="file" name="" class="fileupload">\n' +
    '            <div class="js-fileupload-progress" style="display:none;">\n' +
    '                <i class="icon16 loading"></i><span><!-- upload progress handler --></span>\n' +
    '            </div>\n' +
    '            <span class="errormsg" style="display:none;"><br><br><i class="icon10 no"></i> <span></span></span>\n';

$(document).on('click', '#csvcli .button.yellow', function () {
    $(document).find('#csvcli .fields input[name=file]').parent().html(f);
    $.importexport.csv_productUploadInit();
    $('#s-csvproduct-info').hide().find('.field').first().remove();
    $('#csvcli').find('select[name=encoding],select[name=delimiter]').attr('disabled', false);
    $('#s-csvproduct-info').prev().hide();
    $(this).hide();
    $('#csvcli').removeClass('show-submit');
});

$(document).on('click', '#csvcli .upload-file-url', function(){

    var input = $(this).parent().find('input'),
        url   = $(input).val(),
        pid   = $('#s-csvcli-profile li.selected').data('id') || 0;

    if ( url ){
        $.ajax({
            type: 'POST',
            url: '?plugin=csvcli&module=upload',
            data: {url: url, profile_id: pid},
            success: function(r){
                if ( r.status === 'ok' ){

                    if ( r.data.uploaded ){
                        profile(pid);
                    } else {
                        alert('Загрузка файла не удалась');
                    }

                } else {
                    alert('Загрузка файла не удалась');
                }
            }
        });
    } else {
        alert('Необходимо указать ссылку на файл');
    }

});