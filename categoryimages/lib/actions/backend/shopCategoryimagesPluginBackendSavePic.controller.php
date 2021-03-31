<?php

class shopCategoryimagesPluginBackendSavePicController extends waJsonController
{
    /**
     * Сохраняет изображение категории и если необходимо создает эскизы
     */
    public function execute()
    {
        $message = '';
        $model = new shopCategoryimagesModel();
        $file = waRequest::file('category-image');
        $categoryId = waRequest::post('catId');

        if ($file->uploaded()) {
            $data = array(
                'category_id' => $categoryId,
                'upload_datetime' => date('Y-m-d H:i:s'),
                'file_name' => basename($file->name),
                'size' => $file->size,
                'ext' => $file->extension,
            );
            try {
                $image = $file->waImage();
                $path = wa()->getDataPath("categories/{$categoryId}/", true, 'shop');
                $data['width'] = $image->width;
                $data['height'] = $image->height;
                $data['standard_out'] = waRequest::post('standardOut') ? waRequest::post('standardOut') : 0;
                if ($categoryImage = $model->getByField('category_id', $categoryId)) {
                    waFiles::delete($path  . $categoryImage['id'] . "." . $categoryImage['ext']);
                    $res = $model->updateByField('category_id', $categoryId, $data);
                    $id = $categoryImage['id'];
                } else {
                    $res = $model->insert($data);
                    $id = $res;
                }
                if ($res) {
                    $appSettingsModel = new waAppSettingsModel();
                    
                    $resize = $appSettingsModel->get(array('shop', 'categoryimages'), "resize");
                    $height = $appSettingsModel->get(array('shop', 'categoryimages'), "height");
                    $width = $appSettingsModel->get(array('shop', 'categoryimages'), "width");

                    $resize_big = $appSettingsModel->get(array('shop', 'categoryimages'), "resize_big");
                    $height_big = $appSettingsModel->get(array('shop', 'categoryimages'), "height_big");
                    $width_big = $appSettingsModel->get(array('shop', 'categoryimages'), "width_big");

                    $resize_middle = $appSettingsModel->get(array('shop', 'categoryimages'), "resize_middle");
                    $height_middle = $appSettingsModel->get(array('shop', 'categoryimages'), "height_middle");
                    $width_middle = $appSettingsModel->get(array('shop', 'categoryimages'), "width_middle");

                    $resize_little = $appSettingsModel->get(array('shop', 'categoryimages'), "resize_little");
                    $height_little = $appSettingsModel->get(array('shop', 'categoryimages'), "height_little");
                    $width_little = $appSettingsModel->get(array('shop', 'categoryimages'), "width_little");
                    
                    if ($resize) {
                        $image->resize($width, $height);
                    }
                    if (!$image->save($path . $id . "." . $data['ext'])) {
                        $model->deleteByField('category_id', $data['category_id']);
                        throw new Exception("ошибка загрузки файла", 1);
                    }
                    if ($resize_big) {
                        $image->resize($width_big, $height_big);
                        $image->save($path . $id . "_big." . $data['ext']);
                    }
                    if ($resize_middle) {
                        $image->resize($width_middle, $height_middle);
                        $image->save($path . $id . "_middle." . $data['ext']);
                    }
                    if ($resize_little) {
                        $image->resize($width_little, $height_little);
                        $image->save($path . $id . "_little." . $data['ext']);
                    }
                }
            } catch (Exception $e) {
                $message = "Файл не является изображением, либо произошла другая ошибка: ".$e->getMessage();
                $this->setError($message);
                $model->deleteByField('category_id', $data['category_id']);
                return;
            }
            $message  = "Успешная загрузка";
            $this->response = array (
                'message' => $message,
                'picId' => $id,
                'picExt' => $data['ext'],
                'picUrl' => wa()->getDataUrl("categories/{$categoryId}/", true, 'shop') . $id . '.' . $data['ext'],
            );
        } else {
            if ($categoryImage = $model->getByField('category_id',$categoryId)) {
                if (!waRequest::post('imageExists')) {
                    $path = wa()->getDataPath("categories/{$categoryId}/", true, 'shop');
                    waFiles::delete($path  . $categoryImage['id'] . "." . $categoryImage['ext']);
                    if (!waFiles::listdir($path)) {
                        waFiles::delete($path, true);
                    }
                    $model->deleteById($categoryImage['id']);
                }
                $data['standard_out'] = waRequest::post('standardOut')?waRequest::post('standardOut'):0;
                $model->updateByField('category_id', $categoryId, $data);
            }
        }
    }
}
