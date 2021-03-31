define([
    'backbone',
    'template'
], function(
    Backbone,
    Template
) {
    return Backbone.View.extend({
        tagName: 'tr',
        template: Template('FileRow'),

        render: function() {
            var compiledHTML = this.template(this.model);

            this.$el.html(compiledHTML);
            return this;
        }
    });
});
