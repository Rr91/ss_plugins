var box          = '.catim-ajax-box',
ajaxMenu         = '.catim-ajax-menu',
picMenu          = '.catim-picture-menu',
input            = box + ' input[type="file"]',
catInput         = box + ' input[type="hidden"]',
label            = box + ' .for-category-image',
labelUploading   = box + ' .catim-ajax-uploading-message',
standOutSwitcher   = picMenu + ' #standout_switcher',
delBtn           = '#delete-catpic',
catimPicMenu     = '.catim-picture-menu',
goToManagerBtn   = '.goto-manager-btn',
backendUrlPrefix = '#backend-url-prefix',
droppedFiles     = false,

_csrf = $('[name=_csrf]').val();

showFiles        = function(files) {
    $(labelUploading).text(files.name);
    $(labelUploading).append ('<i class="icon16 loading"></i>');
    $(label).hide('slow');
    $(labelUploading).show('slow');
};

var isAdvancedUpload = function() {
    var div = document.createElement('div');
    return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div) ) && 'FormData' in window && 'FileReader' in window;
}();

function onCatimCatLoad() {
    if(isAdvancedUpload) {
        if (!$(box).hasClass('has-advanced-upload')) {
            $(box).addClass('has-advanced-upload');
        }
    }
}

function sendBoxByAjax() {
    if($(box).hasClass('is-uploading')) {
        return false;
    }

    $(box).addClass('is-uploading').removeClass('is-error');

    var ajaxData = new FormData();
    ajaxData.append('_csrf', _csrf);

    if(droppedFiles) {
        ajaxData.append( $(input).attr('name'), droppedFiles );
        ajaxData.append( $(catInput).attr('name'),  $(catInput).val() );
    }

    $.ajax({
        url: $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=SavePic',
        type: 'POST',
        data: ajaxData,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        complete: function(){
            $(box).removeClass('is-uploading');
        },
        success: function(data) {
            $(box).addClass(data.status == 'ok' ? 'is-success' : 'is-error');
            $(labelUploading).hide({
                duration: 'slow',
                complete: function() {
                    $(label).show('slow');
                }
            });

            if (data.status == 'ok') {
                var picPath = data.data.picUrl;
                $(catimPicMenu).find('a[data-featherlight]').remove();
                $(catimPicMenu).prepend('<a data-featherlight="' + picPath + '"> <img src="' + picPath + ' " id="cat-pic" class="cat-pic-style" /></a>');

                $(ajaxMenu).hide({
                    duration: 'slow',
                    complete: function() {
                        $(picMenu).show('slow');
                    }
                });
            }
        },
        error: function() {
            $(labelUploading).hide({
                duration: 'slow',
                complete: function () {
                    $(label).show('slow');
                }
            });
        }
    });
}

$(document).ready( function() {

    $(document)
        .on('drag dragstart dragend dragover dragenter dragleave drop', box, function(e) {
            if ($(box).hasClass('has-advanced-upload')) {
                e.preventDefault();
                e.stopPropagation();
            }
        })
        .on('drop', box, function( e ) {
            if ($(box).hasClass('has-advanced-upload')) {
                droppedFiles = e.originalEvent.dataTransfer.files[0];
                showFiles( droppedFiles );
                sendBoxByAjax();
            }
        });

    $(document).on('click', goToManagerBtn, function () {
        d.trigger('close');
        window.location.replace($(backendUrlPrefix).val() + '?action=products#/categoryimages/');
    });

    $(document).on('change', input, function(e) {
        showFiles(e.target.files[0]);
        droppedFiles = e.target.files[0];
        sendBoxByAjax();
    });

    $(document).on('change', standOutSwitcher, function() {
        var $this = $(this),
        isChecked = $this.prop('checked');

        $.ajax({
            url: $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=changeOut',
            type: 'POST',
            data: {"standardOut": isChecked, "catId": $(catInput).val()},
            dataType: 'json',
        });
    });

    $(document).on('click', delBtn, function(e) {
        var $this = $(this);
        $this.prop('disabled', true);

        e.stopPropagation();
        e.preventDefault();

        $.ajax({
            url: $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=DeletePic',
            type: 'POST',
            data: {"catId": $(catInput).val(), "_csrf" : _csrf },
            dataType: 'json',
            success: function(data) {
                if (data.status == 'ok') {
                    $(picMenu).hide({
                        duration: 'slow',
                        complete: function() {
                            $(ajaxMenu).show({
                                duration: 'slow',
                                complete: function () {
                                    $this.prop("disabled", false);
                                }
                            });
                        }
                    });
                }
            },
            error: function() {
                $this.prop("disabled", false);
            }
        });
    });
});