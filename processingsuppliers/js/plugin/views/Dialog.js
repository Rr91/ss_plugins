define([
    'backbone'
], function(
    Backbone
) {
    return Backbone.View.extend({
        tagName: 'div',
        className: 'dialog-area processing-suppliers',
        events: {
            'click .cancel': 'remove',
            'click .add': 'add',
            'click .update': 'update',
            'click .delete': 'delete'
        },
        
        add: function() {
            var fields = this.$el.find('form').serializeArray();
            var row = {};

            $.each(fields, function(i, field) {
                row[field.name] = field.value;
            });

            var newModel = this.collection.create(row, {wait: true, at: 0});

            if (!newModel.isValid()) {
                this.showError(newModel.validationError);
                return;
            }

            this.remove();
        },
        update: function() {
            var fields = this.$el.find('form').serializeArray();
            var row = {};

            $.each(fields, function(i, field) {
                row[field.name] = field.value;
            });

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
        delete: function() {
            this.model.destroy({
                wait: true,
                data: {
                    model: JSON.stringify(this.model.toJSON()),
                    _method: 'DELETE'
                }
            });

            this.remove();
        },
        showError: function(errorMsg) {
            this.$el.find('.errormsg').removeClass('hidden').find('span').text(errorMsg); // TODO: плохо - нужно переделать
            // v1.1 scroll to error
            this.$el.find('.dialog-content').animate({ scrollTop: 0 }, 400);
        },
        render: function() {
            var compiledHTML;

            if (this.model) {
                compiledHTML = this.template(this.model);
            } else {
                compiledHTML = this.template();
            }

            this.$el.html(compiledHTML).appendTo(document.body);
            return this;
        }
    });
});
