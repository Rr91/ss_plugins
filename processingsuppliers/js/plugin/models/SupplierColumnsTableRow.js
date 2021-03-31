define([
    'backbone'
], function(
    Backbone
) {
    return Backbone.Model.extend({
        defaults: {
            property: '',
            visibleInUploadTable: false
        },
        
        validate: function(attrs) {
            if (attrs.number < 0) {
                return 'Номер не может быть меньше нуля';
            }
            if (parseInt(attrs.number) < 0) {
                return 'Номер должен быть числом';
            }
        }
    });
});
