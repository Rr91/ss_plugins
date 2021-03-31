define([
    'storage',
    'template',
    'views/Dialog',
    'views/SupplierAddHint',
    'views/SupplierAddHint2',
    'models/SupplierColumnsTableRow',
    'views/SupplierColumnsTableRow',
    'collections/SupplierColumnsTableRows',
    'views/SupplierColumnsTable'
], function(
    Storage,
    Template,
    DialogView,
    SupplierAddHintView,
    SupplierAddHintView2,
    SupplierColumnsTableRowModel,
    SupplierColumnsTableRowView,
    SupplierColumnsTableRowsCollection,
    SupplierColumnsTableView
) {
    return DialogView.extend({
        template: Template('SupplierEdit'),

        events: function() {
            return Object.assign(DialogView.prototype.events, {
                'click .add-hint': 'hint',
                'click .add-hint2': 'hint2',
            });
        },
        initialize: function() {
            this.supplierColumnsTable = new SupplierColumnsTableView({
                collection: new SupplierColumnsTableRowsCollection(JSON.parse(this.model.get('columns')))
            });
        },
        hint: function() {
            var hint = new SupplierAddHintView;
            this.$el.find('.add-hint').before(hint.render().el).remove();
        },
        hint2: function() {
            var hint2 = new SupplierAddHintView2;
            this.$el.find('.add-hint2').before(hint2.render().el).remove();
        },
        update: function() {
            var fields = this.$el.find('form').serializeArray();
            var row = {};

            $.each(fields, function(i, field) {
                row[field.name] = field.value;
            });
            row['columns'] = this.supplierColumnsTable.collection.toJSON();

            this.model.set(row);

            if (!this.model.isValid()) {
                this.showError(this.model.validationError);
                this.model.set(this.model.previousAttributes());
                return;
            }

            if (this.model.hasChanged()) {
                this.model.save();
            }

            this.remove();
        },
        renderColumnsTable: function() {
            this.$el.find('.columns-table').html(this.supplierColumnsTable.render().el);
            return this.supplierColumnsTable;
        },
        render: function() {
            var compiledHTML = this.template(this.model);

            this.$el.html(compiledHTML);
            this.renderColumnsTable();
            return this;
        }
    });
});
