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

class shopProcessingsuppliersFileModel extends waModel
{
    protected $table = 'shop_processingsuppliers_file';

    public function all()
    {
        return $this->order('id DESC')->fetchAll();
    }
    
    public function getSupplierIdById($id)
    {
        return $this->select('supplier_id')->where('id = i:id', array('id' => $id))->fetchField('supplier_id');
    }
    
    public function deleteById($id)
    {
        $id = (int)$id;
        if (!$id) {
            return false;
        }

        $file = $this->getById($id);
        $path = wa()->getDataPath('processingsuppliers', false, 'shop');
        $fileName = $id.'.'.$file['extension'];
        $filePath = $path.'/'.$fileName;
        $fileDeleted = @unlink($filePath);

        if ($fileDeleted) {
            parent::deleteById($id);
            $fileDataModel = new shopProcessingsuppliersFileDataModel();
            $fileDataModel->deleteByField('file_id', $id);
            return true;
        }

        return false;
    }
}