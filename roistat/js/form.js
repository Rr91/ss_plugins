function updateRoistatVisit(param)
{
    var visit_value = $('[name=visit_value]').val();
    var visit_order_id = $('input[name=visit_order_id]').val();
    $.post(param+"shop/?plugin=roistat&module=work&action=visit", { visit_value: visit_value, visit_order_id: visit_order_id }, 
    function(data) {
        if(data == 'ok') {
            $('span.roistatus').html('Изменения сохранены');
            $('span.roistatus').css({'color': '#12D108'});
        } else {
            $('span.roistatus').html('Ошибка');
            $('span.roistatus').css({'color': 'red'});
        }
    });
}