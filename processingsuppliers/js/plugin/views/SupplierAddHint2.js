define([
    'backbone',
    'template'
], function(
    Backbone,
    Template
) {
    return Backbone.View.extend({
        tagName: 'div',
        template: Template('SupplierAddHint2'),

        render: function() {
            var compiledHTML = this.template();

            this.$el.html(compiledHTML);
            return this;
        }
    });
});
