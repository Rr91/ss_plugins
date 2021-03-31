define([
    'backbone',
    'template',
    'views/FileDataRow'
], function(
    Backbone,
    Template,
    FileDataRowView
) {
    return Backbone.View.extend({
        tagName: 'table',
        className: 'zebra',
        template: Template('FileDataTable'),

        initialize: function() {
            this.listenTo(this.collection, 'add', _.debounce(this.render, 128));
        },
        addRow: function(row) {
            var FileDataRow = new FileDataRowView({ model: row });
            
            this.$el.append(FileDataRow.render().el);
        },
        render: function() {
            var compiledHTML = this.template(this.collection);
            
            this.$el.html(compiledHTML);
            this.collection.each(this.addRow, this);
            return this;
        }
    });
});
