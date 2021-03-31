define([
    'backbone',
    'template'
], function(
    Backbone,
    Template
) {
    return Backbone.View.extend({
        tagName: 'tr',
        template: Template('SupplierRow'),

        render: function() {
            var compiledHTML = this.template(this.model.attributes);

            this.$el.html(compiledHTML);
            return this;
        }
    });
});
