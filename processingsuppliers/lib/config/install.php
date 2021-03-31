<?php

try {
    $supplierColumnPropertyModel = new shopProcessingsuppliersSupplierColumnPropertyModel();
    $supplierColumnPropertyGroup = new shopProcessingsuppliersSupplierColumnPropertyGroupModel();
    $supplierColumnPropertyType = new shopProcessingsuppliersSupplierColumnPropertyTypeModel();

    $properties = array(
        array(
            'name' => 'Наименование товара',
            'property' => 'product@name',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Видимость на сайте',
            'property' => 'product@status',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Url',
            'property' => 'product@url',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Тип товара',
            'property' => 'product@type',
            'group' => 1,
            'type' => 1,
            'enabled' => 0,
        ),
        array(
            'name' => 'Категория',
            'property' => 'product@category_id',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Родительская Категория',
            'property' => 'product@sku_parent',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Краткое описание',
            'property' => 'product@summary',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Теги',
            'property' => 'product@tags',
            'group' => 1,
            'type' => 1,
            'enabled' => 0,
        ),
        array(
            'name' => 'Заголовок страницы title',
            'property' => 'product@meta_title',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'META Keywords',
            'property' => 'product@meta_keywords',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'META Description',
            'property' => 'product@meta_description',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Описание',
            'property' => 'product@description',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Картинка',
            'property' => 'product@picture',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Валюта',
            'property' => 'product@currency',
            'group' => 1,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Код артикула',
            'property' => 'sku@sku',
            'group' => 2,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Доступность для покупки',
            'property' => 'sku@available',
            'group' => 2,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Наименование артикула',
            'property' => 'sku@name',
            'group' => 2,
            'type' => 1,
            'enabled' => 1,
        ),
        array(
            'name' => 'Закупочная цена',
            'property' => 'sku@purchase_price',
            'group' => 2,
            'type' => 2,
            'enabled' => 1,
        ),
        array(
            'name' => 'Зачеркнутая цена',
            'property' => 'sku@compare_price',
            'group' => 2,
            'type' => 2,
            'enabled' => 1,
        ),
        array(
            'name' => 'Цена',
            'property' => 'sku@price',
            'group' => 2,
            'type' => 2,
            'enabled' => 1,
        ),
        array(
            'name' => 'Остаток - общий',
            'property' => 'sku@stock[0]',
            'group' => 2,
            'type' => 3,
            'enabled' => 1,
        ),
    );
    $supplierColumnPropertyModel->multipleInsert($properties);

    $groups = array(
        array(
            'name' => 'Параметры товара',
            'enabled' => 1
        ),
        array(
            'name' => 'Параметры артикула',
            'enabled' => 1
        ),
        array(
            'name' => 'Характеристики',
            'enabled' => 1
        ),
    );
    $supplierColumnPropertyGroup->multipleInsert($groups);

    $types = array(
        array('name' => 'string'),
        array('name' => 'price'),
        array('name' => 'stock'),
    );
    $supplierColumnPropertyType->multipleInsert($types);
} catch (waDbException $e) {}