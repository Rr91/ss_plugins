define([
    'backbone'
], function(
    Backbone
) {
    return Backbone.Model.extend({
        defaults: {
            'classes': ''
        },
        
        validate: function(attrs) {
            if (!attrs.url) {
                return 'Для вкладки не задана ссылка';
            }

            if (!attrs.name) {
                return 'Для вкладки не задано имя';
            }
        }
    });
});
