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

class shopProcessingsuppliersFileDataModel extends waModel
{
    protected $table = 'shop_processingsuppliers_file_data';

    public function getByFileId($file_id, $offset = 0, $limit = 0)
    {
        $file_id = (int)$file_id;
        if (!$file_id) {
            return array();
        }

        $offset = (int)$offset;
        $limit = (int)$limit;

        if ($limit) {
            return $this->select('*')
                        ->where('file_id = i:file_id', array('file_id' => $file_id))
                        ->order('id ASC')
                        ->limit($offset.', '.$limit)
                        ->fetchAll();
        } else {
            return $this->select('*')
                        ->where('file_id = i:file_id', array('file_id' => $file_id))
                        ->order('id ASC')
                        ->fetchAll();
        }
    }

    public function setNewProduct($data, $supplierId, $category_id) {
        $product_data = array('status' => 0, 'name' => 'Новый товар', 'currency' => 'RUB', 'sku_id' => -1, 'price' => 0, 'skus' => array(-1=>array('available' => 1, 'price' => 0)), 'category_id' => $category_id, 'categories' => array($category_id));

        if(isset($data['product@name'])) $product_data['name'] = $data['product@name']['value'];
        if(isset($data['product@url'])) $product_data['url'] = $data['product@url']['value'];
        if(isset($data['product@summary'])) $product_data['summary'] = $data['product@summary']['value'];
        if(isset($data['sku@price'])) $product_data['price'] = $data['sku@price']['value'];
        if(isset($data['sku@stock[0]'])) $product_data['count'] = $data['sku@stock[0]']['value'];
        if(isset($data['product@description'])) $product_data['description'] = $data['product@description']['value'];
        if(isset($data['product@currency'])) $product_data['currency'] = $data['product@currency']['value'];
        if(isset($data['product@meta_title'])) $product_data['meta_title'] = $data['product@meta_title']['value'];
        if(isset($data['product@meta_keywords'])) $product_data['meta_keywords'] = $data['product@meta_keywords']['value'];
         
        if(isset($data['sku@sku'])) $product_data['skus'][-1]['sku'] = $data['sku@sku']['value'];
        if(isset($data['sku@name'])) $product_data['skus'][-1]['name'] = $data['sku@name']['value'];
        if(isset($data['sku@purchase_price'])) $product_data['skus'][-1]['purchase_price'] = $data['sku@purchase_price']['value'];
        if(isset($data['sku@compare_price'])) $product_data['skus'][-1]['compare_price'] = $data['sku@compare_price']['value'];
        if(isset($data['sku@price'])) $product_data['skus'][-1]['price'] = $data['sku@price']['value'];
        if(isset($data['sku@stock[0]'])) $product_data['skus'][-1]['count'] = $data['sku@stock[0]']['value'];
        $product = new shopProduct();
        $product->save($product_data);
        return $product->id;
    }
}