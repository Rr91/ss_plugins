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

class shopProcessingsuppliersPluginBackendSupplierRowsController extends waJsonController
{
    protected $supplierModel;
    protected static $allowedMethods = array('get', 'post');
    protected static $allowedEmulatedMethods = array('PUT', 'DELETE');

    public function __construct()
    {
        $this->supplierModel = new shopProcessingsuppliersSupplierModel();
    }

    public function execute()
    {
        if (waRequest::isXMLHttpRequest()) {
            $method = waRequest::getMethod();
            $emulatedMethod = waRequest::post('_method', null, waRequest::TYPE_STRING_TRIM);

            if (!in_array($method, self::$allowedMethods)) {
                throw new waException('Запрещенный метод');
            }

            if ($emulatedMethod && !in_array($emulatedMethod, self::$allowedEmulatedMethods)) {
                throw new waException('Запрещенный эмулированный метод');
            }

            if ($method == 'get') {
                // return rows
                $id = waRequest::get('id', 0, waRequest::TYPE_INT);

                if ($id) {
                    $supplier = $this->supplierModel->getById($id);
                    $this->response = $supplier;
                } else {
                    $suppliers = $this->supplierModel->all();
                    $this->response = $suppliers;
                }
            }

            if ($method == 'post') {
                $modelJSON = waRequest::post('model', null, waRequest::TYPE_STRING_TRIM);
                $model = json_decode($modelJSON, true);
                $error = $this->validate($model);

                if ($error) {
                    throw new waException($error); // TODO: переделать на нормальный вывод ошибок (по идее клиентская проверка не должна сюда пускать)
                } else {
                    if ($emulatedMethod == 'PUT') {
                        // update
                        $id = (int)$model['id'];

                        if (!$id) {
                            throw new waException('Неправильный номер поставщика');
                        }

                        $this->supplierModel->updateById($id, $model);
                    }

                    if ($emulatedMethod == 'DELETE') {
                        // delete
                        $id = (int)$model['id'];

                        if (!$id) {
                            throw new waException('Неправильный номер поставщика');
                        }

                        $this->supplierModel->deleteById($id);
                    }

                    if (!$emulatedMethod) {
                        // create
                        $id = $this->supplierModel->insert($model);
                    }

                    $supplier = $this->supplierModel->getById($id);
                    $this->response = $supplier;
                }
            }
        }
    }

    // v1.1 escape function for string vars
    private function escapeString($string, $trim = false, $upper = false)
    {
        if ($trim) {
            $string = trim($string);
        }

        if ($upper) {
            $string = strtoupper($string);
        }

        return $this->supplierModel->escape($string);
    }

    private function validate(&$model)
    {
        // v1.0 fields
        $model['name'] = $this->escapeString($model['name']);
        $model['first_row'] = (int)$model['first_row'];

        //v1.0 validations
        if (!$model['name']) {
            return 'Не заполнено поле Имя';
        }

        if (!$model['first_row']) {
            return 'Не заполнено поле Номер первой строки';
        }

        // v1.2 validate columns and encode to json
        if (!is_array($model['columns'])) {
            $model['columns'] = json_decode($model['columns'], true);
        }
        foreach ($model['columns'] as $column) {
            $column['column'] = $this->escapeString($column['column'], true, true);
            $column['property'] = $this->escapeString($column['property'], true);

            if (!$column['column'] || !$column['property']) {
                return 'Таблица с привязываемыми свойствами заполнена не полностью';
            }

            if (!$this->parseColumn($column['column'])) {
                return 'Неверный символ в таблице с привязываемыми свойствами';
            }
        }
        $model['columns'] = json_encode($model['columns']);

        // v1.1 fields
        $model['sheet']                     = $this->escapeString($model['sheet'], true);
        $model['update_greater_price']      = (int)$model['update_greater_price'];
        $model['currency']                  = $this->escapeString($model['currency'], true);
        $model['add_markup']                = (int)$model['add_markup'];
        $model['markup_type']               = (int)$model['markup_type'];
        $model['markup']                    = $this->escapeString($model['markup'], true);
        $model['update_sku_availability']   = (int)$model['update_sku_availability'];
        $model['update_product_visibility'] = (int)$model['update_product_visibility'];

        // v1.1 validations
        if (!(int)$model['limit'] && !empty($model['limit'])) {
            return 'Неверно заполнено поле Кол-во обрабатываемых товаров за итерацию';
        }

        if (!(int)$model['limit']) {
            $model['limit'] = null;
        }

        if (!$model['sheet']) {
            $model['sheet'] = null;
        }

        if ($model['update_greater_price'] < 0) {
            $model['update_greater_price'] = null;
        }

        if ($model['add_new'] < 0) {
            $model['add_new'] = null;
        }

        if (!$model['currency']) {
            $model['currency'] = null;
        }

        if ($model['add_markup'] < 0) {
            $model['add_markup'] = null;
        }

        if (!$model['markup_type']) {
            $model['markup_type'] = null;
        }

        if (!$model['markup']) {
            $model['markup'] = null;
        }

        if ($model['update_sku_availability'] < 0) {
            $model['update_sku_availability'] = null;
        }

        if ($model['update_product_visibility'] < 0) {
            $model['update_product_visibility'] = null;
        }

        if (!$model['delimiter']) {
            $model['delimiter'] = null;
        } 

        if (!$model['category_new']) {
            $model['category_new'] = null;
        }
        if (!$model['report_email']) {
            $model['report_email'] = null;
        }

        return false;
    }

    private function parseColumn($value)
    {
        $value = strtoupper($value);

        if (!(int)$value) {
            $charCode = ord($value);

            if ($charCode >= 65 && $charCode <= 90) {
                return true;
            }

            return false;
        }

        return true;
    }
}