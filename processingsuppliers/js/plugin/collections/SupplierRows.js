define([
    'backbone', 
    'models/SupplierRow'
], function(
    Backbone, 
    SupplierRowModel
) {
    return Backbone.Collection.extend({
        model: SupplierRowModel,
        url: '?plugin=processingsuppliers&action=supplierRows',

        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            
            return res;
        }
    });
});
