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

class shopProcessingsuppliersSupplierColumnPropertyModel extends waModel
{
    const SUPPLIER_COLUMN_PROPERTY_GROUP_SKU = 2;

    protected $table = 'shop_processingsuppliers_supplier_column_property';
    protected static $properties = array();

    public function all($group = true)
    {
        $features_model = new shopFeatureModel();
        $features = $features_model->getAll();
        foreach ($features as $f) {
            $property = 'feature@'.$f['code'];
            $count = $this->query("SELECT count(*) FROM shop_processingsuppliers_supplier_column_property WHERE property LIKE '%" . $property . "%' OR name LIKE '%" . $f['name'] . "%'")->fetch();
            $count = array_shift($count);
            if(!$count) {
                $f['property'] = $property;
                $f['type'] = 1;
                $f['group'] = 3;
                $f['enabled'] = 1;
                $properties[$property] = $f;
                $this->addPropery($f);
            }
        }

        $sql = "SELECT
                  `scp`.`id`,
                  `scp`.`name`,
                  `scp`.`property`,
                  `scpg`.`name` AS `group`,
                  `scpt`.`name` AS `type`
                FROM
                  `shop_processingsuppliers_supplier_column_property` AS `scp`
                  LEFT JOIN `shop_processingsuppliers_supplier_column_property_group` AS `scpg`
                    ON `scp`.`group` = `scpg`.`id`
                  LEFT JOIN `shop_processingsuppliers_supplier_column_property_type` AS `scpt`
                    ON `scp`.`type` = `scpt`.`id`
                WHERE `scpg`.`enabled` = 1
                  AND `scp`.`enabled` = 1";

        $properties = $this->query($sql)->fetchAll('property');
        $properties = $this->addStocks($properties);
        if ($group) {
            $properties = $this->groupProperties($properties);
        }

        return $properties;
    }

    public function getProperty($property)
    {
        if (!count(self::$properties)) {
            self::$properties = $this->all(false);
        }

        return self::$properties[$property];
    }

    private function addStocks($properties)
    {
        $stockModel = new shopStockModel();
        $stocks = $stockModel->getAll();
        if (!$stocks) {
            return $properties;
        }

        $supplierColumnPropertyGroupModel = new shopProcessingsuppliersSupplierColumnPropertyGroupModel();
        $group = $supplierColumnPropertyGroupModel->getName(self::SUPPLIER_COLUMN_PROPERTY_GROUP_SKU);
        if (!$group) {
            return $properties;
        }

        foreach ($stocks as $k => $stock) {
            $property = array(
                'id' => $this->countAll() + $k + 1,
                'name' => sprintf("Остаток - %s", $stock['name']),
                'property' => sprintf("sku@stock[%s]", $stock['id']),
                'group' => $group,
                'type' => 'stock'
            );
            $properties[$property['property']] = $property;
        }

        return $properties;
    }

    private function groupProperties($properties)
    {
        if (!$properties) {
            return false;
        }

        foreach ($properties as $k => $p) {
            $properties[$p['group']][$p['property']] = $p;
            unset($properties[$k]);
        }

        return $properties;
    }

    public function addPropery($data)
    {
        $sql = "INSERT INTO `shop_processingsuppliers_supplier_column_property` (
          `name`,
          `property`,
          `group`,
          `type`,
          `enabled`
        )
        VALUES
          (
            '" . $data['name'] . "',
            '" . $data['property'] . "',
            '" . $data['group'] . "',
            '" . $data['type'] . "',
            '" . $data['enabled'] . "'
          ) ;";

        $result = $this->query($sql);

        return true;
    }
}
