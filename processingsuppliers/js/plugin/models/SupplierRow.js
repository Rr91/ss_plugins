define([
    'backbone'
], function(
    Backbone
) {
    return Backbone.Model.extend({
        url: '?plugin=processingsuppliers&action=supplierRows',
        
        validate: function(attrs) {
            if (!attrs.name) {
                return 'Не заполнено поле Имя';
            }
            if (!attrs.first_row) {
                return 'Не заполнено поле Номер первой строки';
            }
            if (!parseInt(attrs.first_row)) {
                return 'Неверно заполнено поле Номер первой строки';
            }

            // v1.2 columns validations
            if (typeof attrs.columns != 'object') {
                attrs.columns = JSON.parse(attrs.columns);
            }
            for (var p in attrs.columns) {
                if (!attrs.columns[p].column || !attrs.columns[p].property) {
                    return 'Таблица с привязываемыми свойствами заполнена не полностью';
                }
                if (!this.validateColumn(attrs.columns[p].column)) {
                    return 'Неверный символ в таблице с привязываемыми свойствами';
                }
            }

            // v1.1 validations
            if (typeof attrs.limit == 'string') {
                if (!parseInt(attrs.limit) && attrs.limit.length > 0) {
                    return 'Неверно заполнено поле Кол-во обрабатываемых товаров за итерацию';
                }
            }
        },
        validateColumn: function(column) {
            column = column.toUpperCase();

            if (!parseInt(column)) {
                var charCode = column.charCodeAt(0);

                return (charCode >= 65 && charCode <= 90);
            }

            return true;
        },
        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            
            return res;
        }
    });
});
