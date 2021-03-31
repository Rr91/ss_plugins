define([
    'backbone',
    'template',
    'views/FileRow'
], function(
    Backbone,
    Template,
    FileRowView
) {
    return Backbone.View.extend({
        el: '.content-area.processing-suppliers',
        template: Template('FilesTable'),

        initialize: function() {
            this.listenTo(this.collection, 'sync', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'add', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'change', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'destroy', _.debounce(this.render, 128));
        },
        addRow: function(row) {
            var FileRow = new FileRowView({ model: row });
            
            this.$el.find('tbody').append(FileRow.render().el);
        },
        render: function() {
            var compiledHTML = this.template();

            this.$el.html(compiledHTML);
            this.collection.each(this.addRow, this);
            return this;
        }
    });
});
