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

class shopProcessingsuppliersSupplierModel extends waModel
{
    protected $table = 'shop_processingsuppliers_supplier';
    
    public function all()
    {
        return $this->order('id DESC')->fetchAll();
    }

    public function deleteById($id)
    {
        $fileModel = new shopProcessingsuppliersFileModel();
        $files = $fileModel->getByField('supplier_id', $id, true);
        foreach ($files as $file) {
            $fileDeleted = $fileModel->deleteById($file['id']);
            if (!$fileDeleted) {
                return false;
            }
        }
        parent::deleteById($id);
        return true;
    }
}
