<?php

/*
 *
 * Processingsuppliers plugin for Webasyst framework.
 *
 * @name Processingsuppliers
 * @author EasyIT LLC
 * @link http://easy-it.ru/
 * @copyright Copyright (c) 2016, EasyIT LLC
 * @version    1.1, 2016-11-22
 *
 */

$supplierModel = new shopProcessingsuppliersSupplierModel();
$supplierModel->exec("ALTER TABLE `shop_processingsuppliers_supplier` 
                        ADD `limit` SMALLINT (5) UNSIGNED NULL,
                        ADD `sheet` VARCHAR (255) NULL,
                        ADD `update_name` TINYINT (1) UNSIGNED NULL,
                        ADD `update_price` TINYINT (1) UNSIGNED NULL,
                        ADD `update_greater_price` TINYINT (1) UNSIGNED NULL,
                        ADD `currency` VARCHAR (10) NULL,
                        ADD `add_markup` TINYINT (1) UNSIGNED NULL,
                        ADD `markup_type` TINYINT (1) UNSIGNED NULL,
                        ADD `markup` VARCHAR (10) NULL,
                        ADD `update_stock` TINYINT (1) UNSIGNED NULL,
                        ADD `stock` TINYINT (1) UNSIGNED NULL,
                        ADD `update_sku_availability` TINYINT (1) UNSIGNED NULL,
                        ADD `update_product_visibility` TINYINT (1) UNSIGNED NULL,
                        ADD `delimiter` VARCHAR (10) NULL");