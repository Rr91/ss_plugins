define([
    'storage',
    'models/Tab',
    'routers/Files'
], function(
    Storage,
    TabModel,
    FilesRouter
) {
    Storage.Tabs.push(new TabModel({
        url: '#/processingsuppliers/files/',
        name: 'Файлы'
    }));

    new FilesRouter;
});
