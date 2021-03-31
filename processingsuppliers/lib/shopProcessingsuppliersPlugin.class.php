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

 
class shopProcessingsuppliersPlugin extends shopPlugin
{
    const DEFAULT_LIMIT                     = 500;
    const DEFAULT_SHEET                     = 'Лист1';
    const DEFAULT_UPDATE_GREATER_PRICE      = 0;
    const DEFAULT_ADD_MARKUP                = 0;
    const DEFAULT_MARKUP_TYPE               = 1;
    const DEFAULT_MARKUP                    = 0;
    const DEFAULT_UPDATE_SKU_AVAILABILITY   = 0;
    const DEFAULT_UPDATE_PRODUCT_VISIBILITY = 0;
    const DEFAULT_DELIMITER                 = ';';
    const DEFAULT_FILE_DATA_TABLE_LIMIT     = 50;



    public static function getCorrectSettings($supplierSettings = array())
    {
        $settings = wa('shop')->getPlugin('processingsuppliers')->getSettings();

        if (isset($supplierSettings['limit'])) {
            $settings['limit'] = $supplierSettings['limit'];
        } else {
            if (empty($settings['limit'])) {
                $settings['limit'] = self::DEFAULT_LIMIT;
            }
        }

        if (isset($supplierSettings['sheet'])) {
            $settings['sheet'] = $supplierSettings['sheet'];
        } else {
            if (empty($settings['sheet'])) {
                $settings['sheet'] = self::DEFAULT_SHEET;
            }
        }

        if (isset($supplierSettings['update_greater_price'])) {
            $settings['update_greater_price'] = $supplierSettings['update_greater_price'];
        } else {
            if (empty($settings['update_greater_price'])) {
                $settings['update_greater_price'] = self::DEFAULT_UPDATE_GREATER_PRICE;
            }
        }

        if (isset($supplierSettings['currency'])) {
            $settings['currency'] = $supplierSettings['currency'];
        } else {
            if (empty($settings['currency'])) {
                $settings['currency'] = wa('shop')->getConfig()->getCurrency();
            }
        }

        if (isset($supplierSettings['add_markup'])) {
            $settings['add_markup'] = $supplierSettings['add_markup'];
        } else {
            if (empty($settings['add_markup'])) {
                $settings['add_markup'] = self::DEFAULT_ADD_MARKUP;
            }
        }

        if (isset($supplierSettings['markup_type'])) {
            $settings['markup_type'] = $supplierSettings['markup_type'];
        } else {
            if (empty($settings['markup_type'])) {
                $settings['markup_type'] = self::DEFAULT_MARKUP_TYPE;
            }
        }

        if (isset($supplierSettings['markup'])) {
            $settings['markup'] = $supplierSettings['markup'];
        } else {
            if (empty($settings['markup'])) {
                $settings['markup'] = self::DEFAULT_MARKUP;
            }
        }

        if (isset($supplierSettings['update_sku_availability'])) {
            $settings['update_sku_availability'] = $supplierSettings['update_sku_availability'];
        } else {
            if (empty($settings['update_sku_availability'])) {
                $settings['update_sku_availability'] = self::DEFAULT_UPDATE_SKU_AVAILABILITY;
            }
        }

        if (isset($supplierSettings['update_product_visibility'])) {
            $settings['update_product_visibility'] = $supplierSettings['update_product_visibility'];
        } else {
            if (empty($settings['update_product_visibility'])) {
                $settings['update_product_visibility'] = self::DEFAULT_UPDATE_PRODUCT_VISIBILITY;
            }
        }

        $settings['markup'] = (int)$settings['markup'];

        // v1.1 new settings
        if (isset($supplierSettings['delimiter'])) {
            $settings['delimiter'] = $supplierSettings['delimiter'];
        } else {
            if (empty($settings['delimiter'])) {
                $settings['delimiter'] = self::DEFAULT_DELIMITER;
            }
        }

        if (empty($settings['file_data_table_limit'])) {
            $settings['file_data_table_limit'] = self::DEFAULT_FILE_DATA_TABLE_LIMIT;
        }

        if (isset($supplierSettings['add_new'])) {
            $settings['add_new'] = $supplierSettings['add_new'];
        }
        if (isset($supplierSettings['category_new'])) {
            $settings['category_new'] = $supplierSettings['category_new'];
        }
        if (isset($supplierSettings['report_email'])) {
            $settings['report_email'] = $supplierSettings['report_email'];
        }
        
        return $settings;
    }
}
