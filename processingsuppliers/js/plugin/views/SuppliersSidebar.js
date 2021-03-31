define([
    'template',
    'views/Sidebar'
], function(
    Template,
    SidebarView
) {
    return SidebarView.extend({
        el: '.sidebar-area.processing-suppliers',
        template: Template('SuppliersSidebar')
    });
});
