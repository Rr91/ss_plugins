define([
    'backbone',
    'models/Tab'
], function(
    Backbone,
    TabModel
) {
    return Backbone.Collection.extend({
        model: TabModel
    });
});
