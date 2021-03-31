define([
    'backbone',
    'models/FileDataRow'
], function(
    Backbone,
    FileDataRowModel
) {
    return Backbone.Collection.extend({
        model: FileDataRowModel,
        url: '?plugin=processingsuppliers&action=fileDataRows',

        parse: function(res) {
            if (res.data) {
                return res.data;
            }

            return res;
        }
    });
});
