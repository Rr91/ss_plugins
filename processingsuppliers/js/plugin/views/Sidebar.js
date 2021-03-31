define([
    'backbone'
], function(
    Backbone
) {
    return Backbone.View.extend({
        className: 'block fields',
        
        render: function() {
            var compiledHTML = this.template(this.model.attributes);
            
            this.$el.html(compiledHTML);
            return this;
        }
    });
});
