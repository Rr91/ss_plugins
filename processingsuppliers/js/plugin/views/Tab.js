define([
    'backbone',
    'template'
], function(
    Backbone,
    Template
) {
    return Backbone.View.extend({
        tagName: 'li',
        template: Template('Tab'),

        className: function() {
            return this.model.get('classes');
        },
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
