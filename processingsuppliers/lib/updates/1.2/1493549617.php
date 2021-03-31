<?php

/*
 *
 * Processingsuppliers plugin for Webasyst framework.
 *
 * @name Processingsuppliers
 * @author EasyIT LLC
 * @link http://easy-it.ru/
 * @copyright Copyright (c) 2017, EasyIT LLC
 * @version    1.2, 2017-04-06
 *
 */

$model = new waModel();

try {
    $model->exec("TRUNCATE TABLE `shop_processingsuppliers_file`");
    $model->exec("TRUNCATE TABLE `shop_processingsuppliers_file_data`");
    $model->exec("TRUNCATE TABLE `shop_processingsuppliers_supplier`");
} catch (waDbException $e) {}

try {
    $model->exec("ALTER TABLE `shop_processingsuppliers_file_data` 
                      DROP COLUMN `sku`,
                      DROP COLUMN `name`,
                      DROP COLUMN `price`,
                      DROP COLUMN `stock`,
                      ADD COLUMN `data` TEXT NULL AFTER `file_id`");
    $model->exec("ALTER TABLE `shop_processingsuppliers_supplier` 
                      DROP COLUMN `column_sku`,
                      DROP COLUMN `column_name`,
                      DROP COLUMN `column_price`,
                      DROP COLUMN `column_stock`,
                      DROP COLUMN `update_name`,
                      DROP COLUMN `update_price`,
                      DROP COLUMN `update_stock`,
                      DROP COLUMN `stock`,
                      ADD COLUMN `columns` TEXT NULL AFTER `name`");
} catch (waDbException $e) {}

try {
    $model->exec("CREATE TABLE `shop_processingsuppliers_supplier_column_property` (
                      `id` INT (10) UNSIGNED NOT NULL AUTO_INCREMENT,
                      `name` VARCHAR (255) NOT NULL,
                      `property` VARCHAR (255) NOT NULL,
                      `group` INT (10) UNSIGNED NOT NULL,
                      `type` INT (10) UNSIGNED NOT NULL,
                      `enabled` TINYINT (1) UNSIGNED NOT NULL DEFAULT 1,
                      PRIMARY KEY (`id`),
                      UNIQUE INDEX `UNIQUE NAME` (`name`),
                      UNIQUE INDEX `UNIQUE PROPERTY` (`property`))");
    $model->exec("CREATE TABLE `shop_processingsuppliers_supplier_column_property_group` (
                      `id` INT (10) UNSIGNED NOT NULL AUTO_INCREMENT,
                      `name` VARCHAR (255) NOT NULL,
                      `enabled` TINYINT (1) UNSIGNED NOT NULL DEFAULT 1,
                      PRIMARY KEY (`id`),
                      UNIQUE INDEX `UNIQUE NAME` (`name`))");
    $model->exec("CREATE TABLE `shop_processingsuppliers_supplier_column_property_type` (
                      `id` INT (10) UNSIGNED NOT NULL AUTO_INCREMENT,
                      `name` VARCHAR (255) NOT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE INDEX `UNIQUE NAME` (`name`))");
} catch (waDbException $e) {}

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
            'property' => 'product@categories',
            'group' => 1,
            'type' => 1,
            'enabled' => 0,
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
            'enabled' => 0,
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
            'enabled' => 0
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