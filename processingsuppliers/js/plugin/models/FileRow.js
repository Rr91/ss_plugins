define([
    'backbone',
    'storage'
], function(
    Backbone,
    Storage
) {
    return Backbone.Model.extend({
        url: '?plugin=processingsuppliers&action=fileRows',

        initialize: function() {
            this.supplier = Storage.SupplierRows.get(this.get('supplier_id'));
        },
        validate: function(attrs) {
            if (!attrs.supplier_id) {
                return 'Не выбран поставщик для файла';
            }
        },
        parse: function(res) {
            if (res.data) {
                return res.data;
            }

            return res;
        }
    });
});
