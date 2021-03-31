define([
    'backbone',
    'template',
    'models/SupplierColumnsTableRow',
    'views/SupplierColumnsTableRow'
], function(
    Backbone,
    Template,
    SupplierColumnsTableRowModel,
    SupplierColumnsTableRowView
) {
    return Backbone.View.extend({
        tagName: 'table',
        className: 'zebra',
        template: Template('SupplierColumnsTable'),
        events: {
            'click .add-row': 'createRow'
        },
        
        createRow: function() {
            var number = this.collection.last().get('number') + 1;
            var row = new SupplierColumnsTableRowModel;

            this.collection.push(row);
            this.addRow(row, number);
        },
        addRow: function(row, number) {
            row.set({number: number}, {validate: true}); // TODO: по идее нужно выдавать ошибку, если валидация не пройдена

            var SupplierColumnsTableRow = new SupplierColumnsTableRowView({ 
                model: row, 
                collection: this.collection 
            });
            
            this.$el.find('tbody').append(SupplierColumnsTableRow.render().el);
        },
        render: function() {
            var compiledHTML = this.template();
            
            this.$el.html(compiledHTML);
            this.collection.each(this.addRow, this);
            return this;
        }
    });
});
