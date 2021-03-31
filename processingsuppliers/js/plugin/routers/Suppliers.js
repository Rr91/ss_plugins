define([
    'backbone',
    'storage',
    'collections/FileRows',
    'collections/SupplierRows',
    'views/SuppliersTable',
    'models/SuppliersSidebar',
    'views/SuppliersSidebar',
    'views/SupplierAdd',
    'views/SupplierEdit',
    'views/SupplierDelete',
    'collections/SupplierColumnsTableRows'
], function(
    Backbone,
    Storage,
    FileRowsCollection,
    SupplierRowsCollection,
    SuppliersTableView,
    SuppliersSidebarModel,
    SuppliersSidebarView,
    SupplierAddView,
    SupplierEditView,
    SupplierDeleteView,
    SupplierColumnsTableRowsCollection
) {
    return Backbone.Router.extend({
        routes: {
            'processingsuppliers/suppliers/': 'showSuppliersTab',
            'processingsuppliers/suppliers/add/': 'showSupplierAdd',
            'processingsuppliers/suppliers/edit/:id': 'showSupplierEdit',
            'processingsuppliers/suppliers/delete/:id': 'showSupplierDelete'
        },

        showSuppliersTab: function() {
            /**
             * FileRows по-умолчанию всегда объявлены в стандартной коллекции Backbone
             * FileRows должны лежать в коллекции типа FileRowsCollection
             * поэтому если FileRows лежат в стандартной коллекции Backbone, то перекладываем их в FileRowsCollection
             */
            if (!(Storage.FileRows instanceof FileRowsCollection)) {
                Storage.FileRows = new FileRowsCollection(Storage.FileRows.toJSON());
            }

            /**
             * проделываем аналогичные действия с SupplierRows
             */
            if (!(Storage.SupplierRows instanceof SupplierRowsCollection)) {
                Storage.SupplierRows = new SupplierRowsCollection(Storage.SupplierRows.toJSON());
            }

            /**
             * Удаляем классы со всех вкладок и добавляем в текущую
             */
            Storage.Tabs.each(function(tab) {
                tab.set('classes', '');
            });
            Storage.Tabs.findWhere({ name: 'Поставщики' }).set('classes', 'selected');

            /**
             * Рендерим таблицу и сайдбар
             */
            new SuppliersTableView({ collection: Storage.SupplierRows }).render();
            new SuppliersSidebarView({ model: new SuppliersSidebarModel }).render();
        },
        showSupplierAdd: function() {
            this.showSuppliersTab();

            var SupplierAdd = new SupplierAddView({ collection: Storage.SupplierRows });

            $(document.body).append(SupplierAdd.render().el);
        },
        showSupplierEdit: function(id) {
            this.showSuppliersTab();

            var SupplierEdit = new SupplierEditView({ 
                model: Storage.SupplierRows.get(id) 
                // collection: Storage.SupplierRows
            });

            $(document.body).append(SupplierEdit.render().el);
        },
        showSupplierDelete: function(id) {
            this.showSuppliersTab();

            var SupplierDelete = new SupplierDeleteView({
                model: Storage.SupplierRows.get(id) 
                // collection: Storage.SupplierRows 
            });

            $(document.body).append(SupplierDelete.render().el);
        }
    });
});
