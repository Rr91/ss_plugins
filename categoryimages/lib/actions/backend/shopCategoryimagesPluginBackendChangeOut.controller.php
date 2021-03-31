<?php

class shopCategoryimagesPluginBackendChangeOutController extends waJsonController
{
    /**
     * Изменяет параметр вывода в стандартном месте (вкл./выкл.) для картинки категории
     */
    public function execute()
    {
        $model = new shopCategoryimagesModel();
        $categoryId = waRequest::post('catId');
        $standOut = waRequest::post('standardOut');

        if (isset($categoryId) && !empty($categoryId) &&
            isset($standOut) && !empty($standOut)) {
            $data['standard_out'] = $standOut == 'true' ? 1 : 0;
            $model->updateByField('category_id', $categoryId, $data);
        }
    }
}

