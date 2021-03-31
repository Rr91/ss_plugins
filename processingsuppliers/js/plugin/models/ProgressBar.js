define(function(require) {
    var Backbone = require('backbone');

    return Backbone.Model.extend({
        defaults: {
            progress: 0,
            message: 'Загрузка...'
        },

        initialize: function(attrs) {
            this.validate(attrs);
        },
        validate: function(attrs) {
            if (attrs.progress !== parseInt(attrs.progress, 10)) {
                this.set('progress', parseInt(attrs.progress, 10));
            }

            if (attrs.progress < 0) {
                return 'Прогресс не может быть меньше 0%';
            }

            if (attrs.progress > 100) {
                return 'Прогресс не может быть больше 100%';
            }
        }
    });
});
