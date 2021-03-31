define([
    'backbone',
    'template'
], function(
    Backbone,
    Template
) {
    return Backbone.View.extend({
        tagName: 'tr',
        template: Template('SupplierColumnsTableRow'),
        events: {
            'click .delete-row': 'delete',
            'change input[type=text]': 'setColumn',
            'change select': 'setProperty',
            'change input[type=checkbox]': 'setVisibilityInUploadTable'
        },

        delete: function() {
            this.collection.remove(this.model);
            this.remove();
        },
        setColumn: function(e) {
            this.model.set({column: e.target.value}, {validate: true}); // TODO: по идее нужно выдавать ошибку, если валидация не пройдена
        },
        setProperty: function(e) {
            this.model.set({property: e.target.value}, {validate: true}); // TODO: по идее нужно выдавать ошибку, если валидация не пройдена
        },
        setVisibilityInUploadTable: function(e) {
            this.model.set({visibleInUploadTable: e.target.checked});
        },
        render: function() {
            var compiledHTML = this.template(this.model);

            this.$el.html(compiledHTML);
            return this;
        }
    });
});
