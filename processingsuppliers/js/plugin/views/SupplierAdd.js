define([
    'template',
    'views/Dialog',
    'models/SupplierColumnsTableRow',
    'views/SupplierColumnsTableRow',
    'views/SupplierAddHint',
    'models/SupplierColumnsTableRow',
    'collections/SupplierColumnsTableRows',
    'views/SupplierColumnsTable'
], function(
    Template,
    DialogView,
    SupplierColumnsTableRowModel,
    SupplierColumnsTableRowView,
    SupplierAddHintView,
    SupplierColumnsTableRow,
    SupplierColumnsTableRowsCollection,
    SupplierColumnsTableView
) {
    return DialogView.extend({
        template: Template('SupplierAdd'),

        events: function() {
            return Object.assign(DialogView.prototype.events, {
                'click .add-hint': 'hint'
            });
        },
        initialize: function() {
            this.supplierColumnsTable = new SupplierColumnsTableView({
                collection: new SupplierColumnsTableRowsCollection([
                    new SupplierColumnsTableRow({
                        number: 0,
                        property: 'sku@sku'
                    })
                ])
            });
        },
        hint: function() {
            var hint = new SupplierAddHintView;
            this.$el.find('.add-hint').before(hint.render().el).remove();
        },
        add: function() {
            var fields = this.$el.find('form').serializeArray();
            var row = {};

            $.each(fields, function(i, field) {
                row[field.name] = field.value;
            });
            row['columns'] = this.supplierColumnsTable.collection.toJSON();

            var newModel = this.collection.create(row, {wait: true, at: 0});

            if (!newModel.isValid()) {
                this.showError(newModel.validationError);
                return;
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
