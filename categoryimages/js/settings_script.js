$(document).ready(function(){

    var $settingsSwitchers = $("#shop-categoryimages-resize, " +
                               "#shop-categoryimages-resize_big, " +
                               "#shop-categoryimages-resize_middle, " +
                               "#shop-categoryimages-resize_little"),
    $recreateBtn           = $('.recreateBtn'),
    $slideTitles           = $('.slide-title'), 
    backendUrlPrefix       = $('#backend-url-prefix').val(),
    pluginControllerLink   =  backendUrlPrefix + 'shop/?plugin=categoryimages&action=RecreatePics';

    _csrf = $('[name=_csrf]').val();

    $settingsSwitchers.change(function() {
        var $this = $(this);

        if ($this.prop('checked')) {
            $this.parents('.fields-group').next('.fields-group').show('slow');
        } else {
            $this.parents('.fields-group').next('.fields-group').hide('slow');
        }
    });

    $recreateBtn.on('click', function() {
        $(this).prop("disabled", true);

        var picType = $(this).data('recreate-pic-type'),
            picWidth = $('input[name="shop_categoryimages[width_'+ picType +']"').val(),
            picHeight = $('input[name="shop_categoryimages[height_'+ picType +']"').val();

        if ( (picType !== undefined && picType != '')
            && (picWidth !== undefined && picWidth != '' && $.isNumeric(picWidth) )
            && (picHeight !== undefined && picHeight != '' && $.isNumeric(picHeight) )) {

            $elem = $(this).next('.recreate-execution-timer');
            $elem.children('.recreate-percentage').html('0%');
            if ($elem.children('.icon16').hasClass('yes')) {
                $elem.children('.icon16').removeClass('yes');
            }
            $elem.children('.icon16').addClass('loading');
            $elem.slideDown('slow');

            $.ajax({
                url: pluginControllerLink,
                type: 'POST',
                global: false,
                data: {"picType": picType, "picWidth": picWidth, "picHeight": picHeight, "_csrf" : _csrf},
                dataType: 'json',
                success: function (data) {
                    send(data.processId,  $elem);
                },
                error: function () {
                    alert('Ошибка загрузки!');
                }
            });

            var saveData = $('#plugins-settings-form').serialize();

            $.ajax({
                url: backendUrlPrefix + 'shop/?module=plugins&id=categoryimages&action=save',
                type: 'POST',
                data: saveData,
                success: function (data) {

                }
            });
        } else {
            $(this).prop("disabled", false);
        }
    });

    function send(processId, $elem) {
        setTimeout(function() {
            $.ajax({
                url: pluginControllerLink + '&processId=' + processId,
                dataType: "json",
                success: function(data){
                    var progress = parseInt(data.progress) + '%';
                    if(!data.ready) {
                        $elem.children('.recreate-percentage').html(progress);
                        send(processId, $elem);
                    } else {
                        $elem.children('.recreate-percentage').html('100%');
                        $elem.children('.icon16').removeClass('loading');
                        $elem.children('.icon16').addClass('yes');
                    }
                }
            });
        }, 1000)
    }

    $('#start_search').click( function(e){
        e.preventDefault();
        $('#percent').html('0%');
        $('.progressbar').css('display', 'inline-block');
        $('.icon16.loading').css('display', 'inline-block');

    });

    $slideTitles.on('click', function() {
        var $this = $(this);
        var tabId =  $this.data('slide-tab-id');
        var $tab = $('.slide-tab-' + tabId);
        var opened = $tab.hasClass('open-tab');

        if (opened) {
            $tab.slideUp('slow');
            $tab.removeClass('open-tab');
            $this.removeClass('open-tab');
        } else {
            $tab.slideDown('slow');
            $tab.addClass('open-tab');
            $this.addClass('open-tab');
        }
    });

    $('.categoryimages-select-output-img').selectric();
});


