<?php

class shopCategoryimagesPluginSettingsAction extends waViewAction
{
    public function execute()
    {
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

        $enabled = $appSettingsModel->get(array('shop', 'categoryimages'), "enabled");
        $output = $appSettingsModel->get(array('shop', 'categoryimages'), "output");
        $outputImg = $appSettingsModel->get(array('shop', 'categoryimages'), "outputImg");

        $this->view->assign('resize', $resize);
        $this->view->assign('height', $height);
        $this->view->assign('width', $width);

        $this->view->assign('resize_big', $resize_big);
        $this->view->assign('height_big', $height_big);
        $this->view->assign('width_big', $width_big);

        $this->view->assign('resize_middle', $resize_middle);
        $this->view->assign('height_middle', $height_middle);
        $this->view->assign('width_middle', $width_middle);

        $this->view->assign('resize_little', $resize_little);
        $this->view->assign('height_little', $height_little);
        $this->view->assign('width_little', $width_little);
        
        $this->view->assign('enabled', $enabled);
        $this->view->assign('output', $output);
        $this->view->assign('outputImg', $outputImg);
    }
}