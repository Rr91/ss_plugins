define([
    'backbone',
    'views/Tab'
], function(
    Backbone,
    TabView
) {
    return Backbone.View.extend({
        el: '.main-tabs.processing-suppliers',

        initialize: function() {
            this.listenTo(this.collection, 'add', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'change', _.debounce(this.render, 128));
            this.listenTo(this.collection, 'remove', _.debounce(this.render, 128));
        },
        addTab: function(tab) {
            var newTab = new TabView({ model: tab });

            this.$el.append(newTab.render().el);
        },
        render: function() {
            this.$el.empty();
            this.collection.each(this.addTab, this);
            return this;
        }
    });
});
