define([
    'backbone',
    'storage',
    'collections/FileRows',
    'collections/SupplierRows',
    'views/FilesTable',
    'models/FilesSidebar',
    'views/FilesSidebar',
    'views/FileAdd',
    'views/FileDelete',
    'views/FileUpload'
], function(
    Backbone,
    Storage,
    FileRowsCollection,
    SupplierRowsCollection,
    FilesTableView,
    FilesSidebarModel,
    FilesSidebarView,
    FileAddView,
    FileDeleteView,
    FileUploadView
) {
    return Backbone.Router.extend({
        routes: {
            'processingsuppliers/': 'showFilesTab',
            'processingsuppliers/files/': 'showFilesTab',
            'processingsuppliers/files/add/': 'showFileAdd',
            'processingsuppliers/files/delete/:id': 'showFileDelete',
            'processingsuppliers/files/upload/:id': 'showFileUpload'
        },

        showFilesTab: function() {
            /**
             * SupplierRows по-умолчанию всегда объявлены в стандартной коллекции Backbone
             * SupplierRows должны лежать в коллекции типа SupplierRowsCollection
             * поэтому если SupplierRows лежат в стандартной коллекции Backbone, то перекладываем их в SupplierRowsCollection
             */
            if (!(Storage.SupplierRows instanceof SupplierRowsCollection)) {
                Storage.SupplierRows = new SupplierRowsCollection(Storage.SupplierRows.toJSON());
            }

            /**
             * проделываем аналогичные действия с FileRows
             */
            if (!(Storage.FileRows instanceof FileRowsCollection)) {
                Storage.FileRows = new FileRowsCollection(Storage.FileRows.toJSON());
            }

            /**
             * Удаляем классы со всех вкладок и добавляем в текущую
             */
            Storage.Tabs.each(function(tab) {
                tab.set('classes', '');
            });
            Storage.Tabs.findWhere({ name: 'Файлы' }).set('classes', 'selected');

            /**
             * Рендерим таблицу и сайдбар
             */
            new FilesTableView({ collection: Storage.FileRows }).render();
            new FilesSidebarView({ model: new FilesSidebarModel }).render();
        },
        showFileAdd: function() {
            this.showFilesTab();

            var FileAdd = new FileAddView({ collection: Storage.FileRows });

            $(document.body).append(FileAdd.render().el);
        },
        showFileDelete: function(id) {
            this.showFilesTab();

            var FileDelete = new FileDeleteView({ model: Storage.FileRows.get(id), collection: Storage.FileRows });

            $(document.body).append(FileDelete.render().el);
        },
        showFileUpload: function(id) {
            this.showFilesTab();

            var FileUpload = new FileUploadView({ model: Storage.FileRows.get(id) });

            $(document.body).append(FileUpload.render().el);
        }
    });
});
