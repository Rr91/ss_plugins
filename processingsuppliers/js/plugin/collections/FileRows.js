define([
    'backbone', 
    'models/FileRow'
], function(
    Backbone, 
    FileRowModel
) {
    return Backbone.Collection.extend({
        model: FileRowModel,
        url: '?plugin=processingsuppliers&action=fileRows',

        parse: function(res) {
            if (res.data) {
                return res.data;
            }
            
            return res;
        }
    });
});
