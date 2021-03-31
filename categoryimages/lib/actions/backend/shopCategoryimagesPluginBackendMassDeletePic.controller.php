<?php

class shopCategoryimagesPluginBackendMassDeletePicController extends waJsonController
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
     * Удаляет изображения и эскизы у всех категорий
     */
    public function execute()
    {
        $sure = waRequest::post('sure');
        if (isset($sure) && !empty($sure)) {
            $model = new shopCategoryimagesModel();
            $catImages = $model->getAll();

            if ($catImages[0]) {

                foreach($catImages as $catImage) {

                    $path = wa()->getDataPath("categories/{$catImage['category_id']}/", true, 'shop');
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
}