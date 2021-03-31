<?php

class shopCategoryimagesPluginBackendMassChangeOutController extends waJsonController
{
    /**
     * Изменяет параметр вывода в стандартном месте (вкл./выкл.) для всех изображений категорий
     */
    public function execute()
    {
        $model = new shopCategoryimagesModel();
        $isChecked = waRequest::post('isChecked');

        if (isset($isChecked) && !empty($isChecked)) {
            $isChecked = $isChecked == 'true' ? 1 : 0;
            $isChecked = $model->escape($isChecked, 'int');
            $model->query("UPDATE " . $model->getTableName() . " SET standard_out = i:isChecked", array('isChecked' => $isChecked));
        }
    }
}