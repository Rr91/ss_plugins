define([
    'backbone',
    'models/SupplierColumnsTableRow'
], function(
    Backbone,
    SupplierColumnsTableRowModel
) {
    return Backbone.Collection.extend({
        model: SupplierColumnsTableRowModel
    });
});
