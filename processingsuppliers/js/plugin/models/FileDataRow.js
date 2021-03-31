define([
    'backbone'
], function(
    Backbone
) {
    return Backbone.Model.extend({
        url: '?plugin=processingsuppliers&action=fileDataRows',
        
        parse: function(res) {
            if (res.data) {
                return res.data;
            }

            return res;
        }
    });
});
