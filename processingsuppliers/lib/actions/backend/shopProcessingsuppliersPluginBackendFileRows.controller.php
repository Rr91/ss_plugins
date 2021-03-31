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

class shopProcessingsuppliersPluginBackendFileRowsController extends waJsonController
{
    protected $fileModel;
    protected static $fileTypes = array('xls', 'xlsx', 'csv', 'xml'); // v1.1 added csv format
    protected static $allowedMethods = array('get', 'post');
    protected static $allowedEmulatedMethods = array('PUT', 'DELETE');

    public function __construct()
    {
        $this->fileModel = new shopProcessingsuppliersFileModel();
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
            }

            if ($method == 'post') {
                $modelJSON = waRequest::post('model', null, waRequest::TYPE_STRING_TRIM);
                $model = json_decode($modelJSON, true);

                if ($emulatedMethod == 'PUT') {
                    // update
                    $id = (int)$model['id'];
                    if (!$id) {
                        throw new waException('Неправильный номер файла');
                    }

                    $this->fileModel->updateById($id, $model);
                }

                if ($emulatedMethod == 'DELETE') {
                    // delete
                    $id = (int)$model['id'];
                    if (!$id) {
                        throw new waException('Неправильный номер файла');
                    }

                    $fileDeleted = $this->fileModel->deleteById($id);
                    if (!$fileDeleted) {
                        throw new waException('Не удалось удалить файл. Попробуйте еще раз');
                    }
                }

                if (!$emulatedMethod) {
                    // create
                    $file = waRequest::file('file');
                    $supplier_id = waRequest::post('supplier_id', 0, waRequest::TYPE_INT);
                    $error = $this->validate();

                    if ($error) {
                        $this->response['error'] = $error;
                    } else {
                        $path = wa()->getDataPath('processingsuppliers', false, 'shop');
                        $name = $this->fileModel->escape($file->name);
                        $extension = $this->fileModel->escape($file->extension);
                        $model = array(
                            'name' => $name,
                            'supplier_id' => $supplier_id,
                            'extension' => $extension
                        );

                        $id = $this->fileModel->insert($model);
                        $newFile = $this->fileModel->getById($id);
                        $newFileName = $id.'.'.$extension;

                        $file->moveTo($path, $newFileName);
                        $this->response = $newFile;
                    }
                }
            }
        }
    }

    private function validate()
    {
        $file = waRequest::file('file');
        $supplier_id = waRequest::post('supplier_id', 0, waRequest::TYPE_INT);
        $extension = $this->fileModel->escape($file->extension);
        $fileTypesString = implode(', ', self::$fileTypes);

        if (!$supplier_id) {
            return 'Не выбран поставщик для файла';
        }

        if (!in_array($extension, self::$fileTypes)) {
            return 'Неверный формат файла. Можно загружать файлы с расширениями '.$fileTypesString;
        }

        if ($file->error) {
            return $file->error;
        }

        if (!$file->uploaded()) {
            return 'Не удалось загрузить файл. Попробуйте еще раз';
        }

        return false;
    }
}