requirejs.config({
    baseUrl: '/wa-apps/shop/plugins/processingsuppliers/js/plugin',
    paths: {
        jquery: 'deps/jquery',
        underscore: '../vendors/underscore/underscore-min',
        backbone: '../vendors/backbone/backbone-min',
        template: 'helpers/template'
    },
    shim: {
        'backbone': {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        },
        'underscore': {
            exports: '_'
        }
    }
});

/**
 * Устанавливаем зависимости между модулями для их последовательной загрузки
 * Зависимости гарантируют загрузку модулей в определенном порядке
 * Все дополнительные модули зависят от штатных, а также от других дополнительных модулей с высшей сортировкой
 */
require(['underscore', 'storage', 'collections/Tabs', 'views/Tabs'], function(_, Storage, TabsCollection, TabsView) {
    Storage.Tabs = new TabsCollection;
    new TabsView({ collection: Storage.Tabs });

    Storage.CoreModules = _.map(Storage.CoreModules, function(module) {
        return 'modules/'+module;
    });
    Storage.CustomModules = _.map(Storage.CustomModules, function(module) {
        return 'modules/'+module;
    });

    /**
     * Первым аргументом в методе union обязательно должны идти штатные модули
     * Это необходимо для того, чтобы штатные модули подгружались первыми, а дополнительные после них
     */
    var AllModules =_.union(Storage.CoreModules, Storage.CustomModules);

    _.each(AllModules, function(module, i) {
        var DepsModules = AllModules.slice(0, i);

        if (DepsModules.length) {
            var config = {shim: {}};

            config['shim'][module] = {deps: DepsModules};
            requirejs.config(config);
        }
    });

    /**
     * После загрузки последнего модуля устанавливаем настройки Backbone и запускаем историю для отслеживания роутов
     * Последним может быть как штатный модуль, так и дополнительный, поэтому в зависимости прописываем последний штатный модуль и последний дополнительный
     */
    require(['backbone', _.last(Storage.CoreModules), _.last(Storage.CustomModules)], function(Backbone) {
        Backbone.emulateHTTP = true;
        Backbone.emulateJSON = true;
        Backbone.history.start();
    });
});


$(document).ready(function(){
    $(".sidebar .block .add-attr").live("click",function(e){
        e.preventDefault();
        console.log("test");
        $.ajax("/webasyst/shop/?plugin=processingsuppliers&action=update");
    });
});