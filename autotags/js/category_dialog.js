(function ($) {
    $.autotags_category_dialog = {
        options: {},
        init: function (options) {
            this.options = options;

            this.initRouteSelector();

            $('#s-product-list-settings-form').on('click', '.helper-link', function () {
                $(this).closest('.value').next('.help-content').slideToggle('slow');
                $(this).find('i.icon10').toggleClass('darr-tiny').toggleClass('uarr-tiny');
                return false;
            });
            return this;
        },
        initRouteSelector: function () {
            $('#route-selector').change(function () {
                var self = $(this);
                var loading = $('<i class="icon16 loading"></i>');
                $(this).attr('disabled', true);
                $(this).after(loading);
                $('.route-container').find('input,select,textarea').attr('disabled', true);
                $('.route-container').slideUp('slow');
                $.get('?plugin=autotags&module=backend&action=categoryDialogRoute&category_id=' + $('input[name=category_id]').val() + '&route_hash=' + $(this).val(), function (response) {
                    $('.route-container').html(response);
                    loading.remove();
                    self.removeAttr('disabled');
                    $('.route-container').slideDown('slow');
                    $('.enabled-engine-checkbox').iButton({
                        labelOn: "Вкл",
                        labelOff: "Выкл",
                        className: 'mini'
                    }).change(function () {
                        $(this).closest('.field-group').next('.field-group').slideToggle('slow');
                    });

                    var fields = $('.route-container .field.description');
                    fields.find('i').hide();
                    fields.find('.s-editor-core-wrapper').show();
                    fields.find('textarea').waEditor({
                        lang: 'ru',
                        toolbarFixedBox: false
                    });
                });
                return false;
            }).change();
        }
    };
})(jQuery);
