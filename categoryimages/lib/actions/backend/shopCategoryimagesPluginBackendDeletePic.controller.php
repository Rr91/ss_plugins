<?php

class shopCategoryimagesPluginBackendDeletePicController extends waJsonController
{
    /**
     * Возвращает путь до картинки категории
     * @param $path - путь до папки с картинками
     * @param $catImage - имя файла картинки
     * @param bool $thumb - оригинал или превью
     * @return string $picPath - путь до картинки категории
     */
    private function getPicPath($path, $catImage, $thumb = false)
    {
        $picPath = $path  . $catImage['id'];

        if ($thumb) {
            $picPath .= "_" . $thumb;
        }

        $picPath .= "." . $catImage['ext'];
        return $picPath;
    }

    /**
     * Удаляет изображение и эскизы категории
     */
    public function execute()
    {
        $categoryId = waRequest::post('catId');
        if (isset($categoryId) && !empty($categoryId)) {
            $model = new shopCategoryimagesModel();
            $catImage = $model->getByField('category_id', $categoryId);

            if ($catImage['id']) {
                $path = wa()->getDataPath("categories/{$categoryId}/", true, 'shop');
                $model->deleteById($catImage['id']);

                if (file_exists($this->getPicPath($path, $catImage))) {
                    waFiles::delete($this->getPicPath($path, $catImage));
                }
                if (file_exists($this->getPicPath($path, $catImage, 'big'))) {
                    waFiles::delete($this->getPicPath($path, $catImage, 'big') );
                }
                if (file_exists($this->getPicPath($path, $catImage, 'middle'))) {
                    waFiles::delete($this->getPicPath($path, $catImage, 'middle') );
                }
                if (file_exists($this->getPicPath($path, $catImage, 'little'))) {
                    waFiles::delete($this->getPicPath($path, $catImage, 'little') );
                }
                if (!waFiles::listdir($path)) {
                    waFiles::delete($path, true);
                }
            }
        }
    }
}