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

class shopProcessingsuppliersSupplierColumnPropertyGroupModel extends waModel
{
    protected $table = 'shop_processingsuppliers_supplier_column_property_group';

    public function getName($id)
    {
        if (!$id) {
            return false;
        }

        return $this->select('name')->where('id = ?', (int)$id)->fetchField('name');
    }
}
