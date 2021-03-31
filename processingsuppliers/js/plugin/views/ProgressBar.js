define(function(require) {
    var Backbone = require('backbone');
    var template = require('template');

    return Backbone.View.extend({
        tagName: 'div',
        className: 'progressbar processing-suppliers',
        template: template('ProgressBar'),

        initialize: function() {
            this.listenTo(this.model, 'change', _.debounce(this.render, 128));
        },
        render: function() {
            var compiledHTML = this.template(this.model.attributes);

            this.$el.html(compiledHTML);
            return this;
        }
    });
});
