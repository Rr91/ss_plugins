$(document).ready(function() {
    var searchBox             = '.catim-search-box',
    searchBtn                 = '.catim-search-box-magnifier',
    catFirstList              = '#catim-categories-first-list',
    searchMagnifierWrapper    = '.catim-search-box-magnifier-wrapper',
    showAllCatBtn             = '.catim-show-all-btn',
    searchMagnifierIco        = '.catim-search-box-magnifier',
    searchLoadingIco          = '.catim-search-box-loading',
    delBtn                    = '.catim-folder-del-btn',
    uploadBtn                 = '.catim-folder-btn-file',
    catimTitle                = '.catim-cat-folder-title',
    uploadBtnLabel            = '.catim-folder-btn-file-label',
    standOutSwitch            = '.ios-switch',
    standOutSwitchBox         = '.ios-switch-box',
    massDelBtn                = '.catim-mass-del-btn',
    massSwitch                = '.ios-switch-mass',
    loadingIco                = '.catim-filebrowser-loading',
    catPic                    = '.catim-cat-pic', 
    urlPrefix                 = '#url-prefix',
    backendUrlPrefix          = '#backend-url-prefix',
    defPicPath                = 'wa-apps/shop/plugins/categoryimages/img/no-image-pic.png',
    hiddenDidntMatchText      = '#hidden-didnt-match-text',
    hiddenDelBtnText          = '#hidden-del-btn-text',
    hiddenOrText              = '#hidden-or-text',
    hiddenCancelBtnText       = '#hidden-cancel-btn-text',
    hiddenDelMsg              = '#hidden-del-msg',
    hiddenShowAllBtnText      = '#hidden-show-all-btn-text',
    hiddenShowAllHideBtnText = '#hidden-show-all-hide-btn-text',
    massSwitchIsOn            = false,
    droppedFiles              = false,
    disableSearch             = false;

    _csrf = $('[name=_csrf]').val();

    $(document).on('click', searchBtn, function() {
        if (disableSearch) {
            return;
        }

        disableSearch = true;
        searchQuery = $(searchBox).val();
        searchQuery = searchQuery.toLowerCase();

        if ($(showAllCatBtn).hasClass('showing')) {
            $(showAllCatBtn).removeClass('showing');
            $(showAllCatBtn).html($(hiddenShowAllBtnText).val());
        }

        $(searchMagnifierWrapper).find(searchMagnifierIco).hide({
            duration: 0,
            complete: function() {
                $(searchMagnifierWrapper).find(searchLoadingIco).show({
                    duration : 0
                });
            }
        });

        if (searchQuery != '' && searchQuery !== undefined) {
            $.ajax({
                url: $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=SearchCategories',
                type: 'POST',
                data: {"searchQuery": searchQuery, "default": 'false', "_csrf" : _csrf },
                dataType: 'json',
                success: function( data ) {
                    if (data.status == 'ok' && data.data.html != '') {
                        $(catFirstList).velocity({
                            width: 0,
                            opacity: 0
                        }, {
                            display: 'none',
                            ease: 'easeInSine',
                            duration: 500,
                            complete: function() {
                                $(catFirstList).empty();
                                $(catFirstList).append(data.data.html);
                                $(catFirstList).velocity({
                                    width: '100%',
                                    opacity: 1
                                }, {
                                    display: 'block',
                                    ease: 'easeInSine',
                                    duration: 500,
                                    complete: function() {
                                        $(searchMagnifierWrapper).find(searchLoadingIco).hide({
                                            duration: 0,
                                            complete: function () {
                                                $(searchMagnifierWrapper).find(searchMagnifierIco).show();
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    } else {
                        $.featherlight('<div style="overflow: hidden; padding: 50px;">' + $(hiddenDidntMatchText).val() + '</div>', {type: 'html'});
                        $(searchMagnifierWrapper).find(searchLoadingIco).hide({
                            duration: 0,
                            complete: function () {
                                $(searchMagnifierWrapper).find(searchMagnifierIco).show();
                            }
                        });
                    }
                    disableSearch = false;
                },
                error: function() {
                    $(searchMagnifierWrapper).find(searchLoadingIco).hide({
                        duration: 0,
                        complete: function () {
                            $(searchMagnifierWrapper).find(searchMagnifierIco).show();
                        }
                    });
                    disableSearch = false;
                }
            });
        } else {
            $.featherlight('<div style="overflow: hidden; padding: 50px;">' + $(hiddenDidntMatchText).val() + '</div>', {type: 'html'});
            $(searchMagnifierWrapper).find(searchLoadingIco).hide({
                duration: 0,
                complete: function () {
                    $(searchMagnifierWrapper).find(searchMagnifierIco).show();
                }
            });
            disableSearch = false;
        }
    });

    $(document).on('keyup', searchBox, function(e) {
        if (e.keyCode == 13 && !disableSearch) {
            $(searchBtn).trigger("click");
        }
    });

    $(document).on('input', searchBox, function() {
        if ( $(this).val() == '') {
            $(searchBtn).prop('disabled', true);

            if ($(showAllCatBtn).hasClass('showing')) {
                $(showAllCatBtn).removeClass('showing');
                $(showAllCatBtn).html($(hiddenShowAllBtnText).val());
            }

            $.ajax({
                url:  $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=SearchCategories',
                type: 'POST',
                data: {"searchQuery": 'default', "default" : 'true', "_csrf" : _csrf },
                dataType: 'json',
                success: function( data ) {
                    if (data.status == 'ok' && data.data.html != '') {
                        $(catFirstList).velocity({
                            width: 0,
                            opacity: 0
                        }, {
                            display: 'none',
                            ease: 'easeInSine',
                            duration: 500,
                            complete: function() {
                                $(catFirstList).empty();
                                $(catFirstList).append(data.data.html);
                                $(catFirstList).velocity({width: '100%', opacity: 1}, {display: 'block', ease: 'easeInSine', duration: 500});
                            }
                        });
                    }
                    $(searchBtn).prop('disabled', false);
                },
                error: function() {
                    $(searchBtn).prop('disabled', false);
                }
            });
        }
    });

    $(document).on('click', showAllCatBtn, function() {
        var $this = $(this);

        $this.prop('disabled', true);
        disableSearch = true;

        if ($this.hasClass('showing')) {
            $.ajax({
                url:  $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=SearchCategories',
                type: 'POST',
                data: {"searchQuery": 'default', "default" : 'true', "_csrf" : _csrf },
                dataType: 'json',
                complete: function() {
                    disableSearch = false;
                },
                success: function( data ) {
                    if (data.status == 'ok' && data.data.html != '') {
                        $(catFirstList).velocity({
                            width: 0,
                            opacity: 0
                        }, {
                            display: 'none',
                            ease: 'easeInSine',
                            duration: 500,
                            complete: function() {
                                $(catFirstList).empty();
                                $(catFirstList).append(data.data.html);
                                $(catFirstList).velocity({width: '100%', opacity: 1}, {display: 'block', ease: 'easeInSine', duration: 500});
                            }
                        });
                    }
                    $this.html($(hiddenShowAllBtnText).val());
                    $this.removeClass('showing');
                    $this.prop('disabled', false);
                },
                error: function() {
                    $this.prop('disabled', false);
                }
            });
        } else {
            $.ajax({
                url:  $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=SearchCategories',
                type: 'POST',
                data: {"searchQuery": 'default', "default" : 'all', "_csrf" : _csrf },
                dataType: 'json',
                complete: function() {
                    disableSearch = false;
                },
                success: function( data ) {
                    if (data.status == 'ok' && data.data.html != '') {
                        $(catFirstList).velocity({
                            width: 0,
                            opacity: 0
                        }, {
                            display: 'none',
                            ease: 'easeInSine',
                            duration: 500,
                            complete: function() {
                                $(catFirstList).empty();
                                $(catFirstList).append(data.data.html);
                                $(catFirstList).velocity({width: '100%', opacity: 1}, {display: 'block', ease: 'easeInSine', duration: 500});
                            }
                        });
                    }
                    $this.addClass('showing');
                    $this.html($(hiddenShowAllHideBtnText).val());
                    $this.prop('disabled', false);
                },
                error: function() {
                    $this.prop('disabled', false);
                }
            });
        }
    });

    $(document).on('change', uploadBtn ,function(e) {
        var $this = $(this),
            catId = $this.data('cat-id'),
            $catBox = $this.parents('.catim-folder-box');

        $this.prop('disabled', true);

        if (catId !== undefined && catId != '') {

            droppedFiles = e.target.files[0];

            var $picPlace = $catBox.find('.catim-folder-pic-place');

            $picPlace.find(catPic).hide({
                duration: 0,
                complete: function() {
                    $picPlace.find(loadingIco).show({
                        duration: 0,
                        start: function () {
                            $picPlace.find(loadingIco).css('display', 'inline-block');
                        }
                    });
                }
            });

            var ajaxData = new FormData();
            ajaxData.append('_csrf', _csrf);

            if (droppedFiles) {
                ajaxData.append('category-image', droppedFiles);
                ajaxData.append('catId', catId);
            }

            $.ajax({
                url: $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=SavePic',
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                complete: function () {
                    $this.prop('disabled', false);
                },
                success: function (data) {
                    if (data.status == 'ok') {
                        var picPath =  data.data.picUrl;
                        $picPlace.find(loadingIco).hide({
                            duration: 400,
                            complete: function () {
                                $picPlace.find(catPic).attr('src', picPath);
                                $picPlace.find(catPic).attr('data-featherlight', picPath);
                                $picPlace.find(catPic).addClass('isPicture');
                                $picPlace.find(catPic).show();
                            }
                        });

                        $catBox.addClass('isPicture');

                        $catBox.find(uploadBtn).hide('slow');
                        $catBox.find(uploadBtn).addClass('isPicture');

                        $catBox.find(uploadBtnLabel).hide('slow');
                        $catBox.find(uploadBtnLabel).addClass('isPicture');

                        $catBox.find(standOutSwitchBox).show('slow');
                        $catBox.find(standOutSwitchBox).addClass('isPicture');

                        $catBox.find(delBtn).show('slow');
                        $catBox.find(delBtn).addClass('isPicture');
                    } else {
                        $picPlace.find(loadingIco).hide({
                            complete: function () {
                                $picPlace.find(catPic).show();
                            }
                        });
                    }
                }
            });
        } else {
            $this.prop('disabled', false);
        }
    });

    $(document).on('click', delBtn , function(e) {
        var $this = $(this),
        catId = $(this).data('cat-id'),
        $picPlace = $(this).parents('.catim-folder-box').find('.catim-folder-pic-place'),
        $catBox = $(this).parents('.catim-folder-box');

        $this.prop('disabled', true);

        e.stopPropagation();
        e.preventDefault();

        if (catId === undefined || catId == '') {
            return;
        }

        $picPlace.find(catPic).hide({
            duration: 0,
            complete: function() {
                $picPlace.find(loadingIco).show({
                    duration : 0,
                    start: function() {
                        $picPlace.find(loadingIco).css('display', 'inline-block');
                    }
                });
            }
        });

        $.ajax({
            url:  $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=DeletePic',
            type: 'POST',
            data: {"catId": catId, "_csrf" : _csrf},
            dataType: 'json',
            success: function( data ) {
                if (data.status == 'ok') {
                    $picPlace.find(loadingIco).hide({
                        duration: 400,
                        complete: function () {
                            var src = $(urlPrefix).val() + defPicPath;
                            console.log(src);
                            $picPlace.find(catPic).attr('src', src);
                            $picPlace.find(catPic).removeAttr('data-featherlight');
                            $picPlace.find(catPic).removeClass('isPicture');
                            $picPlace.find(catPic).show();
                        }
                    });

                    $catBox.find(standOutSwitchBox).hide();
                    $catBox.find(standOutSwitchBox).removeClass('isPicture');

                    $catBox.find(delBtn).hide('slow', function () {
                        $this.prop('disabled', false);
                    });

                    $catBox.find(delBtn).removeClass('isPicture');

                    $catBox.find(uploadBtnLabel).show('slow');
                    $catBox.find(uploadBtnLabel).removeClass('isPicture');

                    $catBox.find(uploadBtn).show('slow');
                    $catBox.find(uploadBtn).removeClass('isPicture');
                } else {
                    $this.prop('disabled', false);
                    $picPlace.find(loadingIco).hide({
                        complete: function () {
                            $picPlace.find(catPic).show();
                        }
                    });
                }
            },
            error: function() {
                $this.prop('disabled', false);
            }
        });
    });

    $(document).on('change', standOutSwitch , function() {
        var $this = $(this),
            isChecked = $this.prop('checked'),
            catId = $this.data('cat-id');

        if (massSwitchIsOn) {
            return;
        }

        $this.prop('disabled', true);

        $.ajax({
            url:  $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=changeOut',
            type: 'POST',
            data: {"standardOut": isChecked, "catId": catId, "_csrf" : _csrf},
            dataType: 'json',
            complete: function() {
                $this.prop('disabled', false);
            }
        });
    });

    $(document).on('click', massDelBtn, function(e) {
        e.stopPropagation();
        e.preventDefault();

        $('<p class="del-message">' + $(hiddenDelMsg).val() + '</p>').waDialog({
            height: '100px',
            width: '450px',
            buttons: '<input class="button red" type="submit" value="' + $(hiddenDelBtnText).val() + '" /> ' + $(hiddenOrText).val() + ' <a class="cancel" href="#">' + $(hiddenCancelBtnText).val() + '</a>',
            onSubmit: function (d) {

                d.trigger('close');

                var $catBoxesWithPic = $('.catim-folder-box.isPicture');
                var $picPlace = $catBoxesWithPic.find('.catim-folder-pic-place');

                $picPlace.find(catPic).hide({
                    duration: 0,
                    complete: function() {
                        $picPlace.find(loadingIco).show({
                            duration: 0,
                            start: function () {
                                $picPlace.find(loadingIco).css('display', 'inline-block');
                            }
                        });
                    }
                });

                $.ajax({
                    url:  $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=MassDeletePic',
                    type: 'POST',
                    data: {"sure": 'true', "_csrf" : _csrf},
                    dataType: 'json',
                    success: function( data ) {
                        if (data.status == 'ok') {
                            $picPlace.find(loadingIco).hide({
                                duration: 0,
                                complete: function () {
                                    var src = $(urlPrefix).val() + defPicPath;
                                    $picPlace.find(catPic).attr('src', src);
                                    $picPlace.find(catPic).removeAttr('data-featherlight');
                                    $picPlace.find(catPic).removeClass('isPicture');
                                    $picPlace.find(catPic).show();
                                }
                            });

                            $catBoxesWithPic.find(standOutSwitchBox).hide();
                            $catBoxesWithPic.find(standOutSwitchBox).removeClass('isPicture');

                            $catBoxesWithPic.find(delBtn).hide('slow');
                            $catBoxesWithPic.find(delBtn).removeClass('isPicture');

                            $catBoxesWithPic.find(uploadBtnLabel).show('slow');
                            $catBoxesWithPic.find(uploadBtnLabel).removeClass('isPicture');

                            $catBoxesWithPic.find(uploadBtn).show('slow');
                            $catBoxesWithPic.find(uploadBtn).removeClass('isPicture');
                        } else {
                            $picPlace.find(loadingIco).hide({
                                complete: function () {
                                    $picPlace.find(catPic).show();
                                }
                            });
                        }
                    }
                });
                return false;
            }
        });
    });

    $(document).on('change', massSwitch, function() {
        var $this = $(this),
            isChecked = $this.prop('checked');

        $this.prop('disabled', true);

        $.ajax({
            url:  $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=massChangeOut',
            type: 'POST',
            data: {"isChecked": isChecked, "_csrf" : _csrf},
            dataType: 'json',
            complete: function() {
                massSwitchIsOn = false;
            },
            success: function( data ) {
                if (data.status == 'ok') {
                    $(standOutSwitch).prop('checked', isChecked);
                }
                $this.prop('disabled', false);
            },
        });
    });

    $(document).on('click', catimTitle, function() {
        var $this = $(this),
            catId = $this.data('cat-id'),
            childrenIsset = $this.parents('.catim-list-category').eq(0).children('.catim-list-category').length;

            if ($this.hasClass('without-arrow') || $this.hasClass('in-progress')) {
                return;
            }

            $this.addClass('in-progress');

            if (childrenIsset != 0 ) {
                $this.parents('.catim-list-category').eq(0).children('.catim-list-category').remove();
                $this.removeClass('open-cat');
                $this.removeClass('in-progress');
            } else {
                if (catId != '' && catId !== undefined) {
                    $.ajax({
                        url:  $(backendUrlPrefix).val() + 'shop/?plugin=categoryimages&action=GetCategoryChildren',
                        type: 'POST',
                        data: {"catId": catId , "_csrf" : _csrf},
                        dataType: 'json',
                        success: function( data ) {
                            if (data.status == 'ok' && data.data.html != '') {
                                $this.parents('.catim-list-category').eq(0).append(data.data.html);
                                $this.addClass('open-cat');
                            }
                            $this.removeClass('in-progress');
                        },
                        error: function() {
                            $this.removeClass('in-progress');
                        }
                    });
                }
            }
    });

    $(document).on('mouseenter', '.catim-tooltip:not(.tooltipstered)', function(){
        $(this).tooltipster({
            theme: 'tooltipster-shadow',
            position: 'left',
            timer: 0
        })
        .tooltipster('open');
    });

});







