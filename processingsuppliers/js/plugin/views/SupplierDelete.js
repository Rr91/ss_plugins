define([
    'template',
    'views/Dialog'
], function(
    Template,
    DialogView
) {
    return DialogView.extend({
        template: Template('SupplierDelete')
    });
});
