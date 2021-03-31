$(function () {
    $.customers.reviewsAction = function (p) {
        this.load('?plugin=reviews' + (p ? '&page=' + p : ''), function () {
            $("#s-reviews li.selected").removeClass('selected');
            $("#s-reviews").addClass('selected');

            $("a.s-review-reply").click(function () {
                var f = $("#s-review-add-form");
                f.find('.date').val('').datepicker({
                   altField: "#s-review-response-add-date",
                   altFormat: "yy-mm-dd"
                });
                f.find('.time').val('');
                f.insertAfter(this).show().find('textarea').val('');
                return false;
            });

            $('#s-review-edit-form span.rate').rateWidget({
                onUpdate: function(rate) {
                    $('#s-review-edit-form input[name="rating"]').val(rate);
                }
            });

            $(".s-reviews").on('click', "a.s-review-edit", function () {
                var d = $(this).closest('div').hide();
                if (d.hasClass('response')) {
                    d.find('.s-review-text span').find('br').remove();
                    var f = $("#s-review-response-edit-form").insertAfter(d).show();
                    f.find('textarea').val(d.find('.s-review-text span').html()).focus();
                    f.find('.date').val(d.data('date')).datepicker({
                        altField: "#s-review-response-edit-date",
                        altFormat: "yy-mm-dd"
                    });
                    f.find('.time').val(d.data('time'));
                } else {
                    var p = d.closest('.s-review');
                    var f = $("#s-review-edit-form").insertAfter(d).show();
                    f.find('input:file').each(function () {
                      $(this).replaceWith($(this).clone());
                    });
                    f.attr('action', '?plugin=reviews&action=save&id=' + $(this).closest('li').data('id'));
                    f.find('textarea').val(d.find('.s-review-text span').text()).focus();
                    f.find('input.name').val(p.find('.details .name').text());
                    f.find('.date').val(d.data('date')).datepicker({
                        altField: "#s-review-edit-date",
                        altFormat: "yy-mm-dd"
                    });
                    var r = p.find('.details .rate').data('rating') || 0
                    f.find('span.rate').rateWidget(
                        'setOption', 'rate', r
                    );
                    f.find('input[name=rating]').val(r);
                    f.find('.time').val(d.data('time'));
                }
                return false;
            });

            $("#s-review-edit-form").submit(function () {
                var li = $(this).closest('li');
                $("#review-iframe").one('load', function () {
                    try {
                        var data = $(this).contents().find('body').html();
                        var response = $.parseJSON(data);
                    } catch (e) {
                        return;
                    }
                    if (response.status == 'ok') {
                        li.find('div.text p.s-review-text span').html(response.data.text);
                        li.find('div.text').show();
                        if (response.data.datetime) {
                            li.find('div.text').data('date', response.data.date);
                            li.find('div.text').data('time', response.data.time);
                            li.find('.datetime').html(response.data.datetime);
                        }
                        if (response.data.image) {
                            li.find('.profile').addClass('image20px');
                            li.find('.profile img.image').attr('src', response.data.image + '?v' + Math.random()).show();
                        }
                        if (response.data.name) {
                            li.find('.profile .details .name').html(response.data.name);
                        }
                        li.find('.profile .details .rate').data('rating', response.data.rating);
                        var rate = 0;
                        li.find('.profile .details .rate').find('i')
                            .removeClass('star star-hover')
                            .addClass('star-empty').each(function() {
                            if (rate == response.data.rating) {
                                return false;
                            }
                            rate++;
                            $(this).removeClass('star-empty').addClass('star');
                        });
                        if (response.data.images) {
                            var html = '';
                            for (var i = 0; i < response.data.images.length; i++) {
                                html += '<div class="image"><img src="' + response.data.images[i] + '"></div>';
                            }
                            li.find('div.images').html(html);
                        }
                        $("#s-review-edit-form").hide().insertAfter($('div.s-reviews ul:first'));
                    }
                });
            });

            $("#s-review-response-edit-form").submit(function () {
                var li = $(this).closest('li');
                $.post('?plugin=reviews&action=save&id=' + li.data('id'), $(this).serialize(), function (response) {
                    if (response.status == 'ok') {
                        li.find('div.response p.s-review-text span').html(response.data.response);
                        li.find('div.response').show();
                        if (response.data.response_datetime) {
                            li.find('div.response').data('date', response.data.response_date);
                            li.find('div.response').data('time', response.data.response_time);
                            li.find('div.response .datetime').html(response.data.response_datetime);
                        }
                        $("#s-review-response-edit-form").hide().insertAfter($('div.s-reviews ul:first'));
                    }
                }, 'json');

                return false;
            });


            $("#s-review-add-form").submit(function () {
                var li = $(this).closest('li');
                $.post('?plugin=reviews&action=save&id=' + li.data('id'), $(this).serialize(), function (response) {
                    if (response.status == 'ok') {
                        li.find('a.s-review-reply').replaceWith(
                          '<div class="response" style="margin-left: 10px; padding-left: 10px; border-left: 1px solid #ccc">' +
                          (response.data.response_datetime ? '<span class="hint"><span class="datetime">' + response.data.response_datetime + '</span></span>' : '') +
                          '<p class="s-review-text"><span>' + response.data.response + '</span> ' +
                          '<a style="margin-left:0;" href="#" class="small s-review-edit" ><i class="icon10 edit"></i></a></p></div>'
                        );
                        $("#s-review-add-form").hide().insertAfter($('div.s-reviews ul:first'));
                    }
                }, 'json');

                return false;
            });

            $("a.s-review-approve").click(function () {
                var li = $(this).closest('li');
                $.post('?plugin=reviews&action=save&id=' + li.data('id'), {status: 1}, function (response) {
                    if (response.status == 'ok') {
                        li.find('.highlighted').removeClass('highlighted');
                        li.find('a.s-review-approve').fadeOut(function () {
                            $(this).remove();
                        });
                    }
                }, 'json');
                return false;
            });
            $("a.s-review-delete").click(function () {
                if (confirm($_('Are you sure?'))) {
                    var li = $(this).closest('li');
                    $.post('?plugin=reviews&action=delete', {id: li.data('id')}, function (response) {
                        li.fadeOut(function () {
                           $(this).remove();
                        });
                    }, "json");
                }
                return false;
            });
        });
    }
});