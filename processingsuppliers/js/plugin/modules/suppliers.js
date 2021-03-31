define([
    'storage',
    'models/Tab',
    'routers/Suppliers'
], function(
    Storage,
    TabModel,
    SuppliersRouter
) {
    Storage.Tabs.push(new TabModel({
        url: '#/processingsuppliers/suppliers/',
        name: 'Поставщики'
    }));

    new SuppliersRouter;
});
