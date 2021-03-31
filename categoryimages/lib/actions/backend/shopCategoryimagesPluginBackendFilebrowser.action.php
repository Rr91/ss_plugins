<?php

class shopCategoryimagesPluginBackendFilebrowserAction extends waViewAction
{
    /**
     * Передает в шаблон информацию о изображениях родительских категориий
     */
    public function execute()
    {
        $categoriesClass = new shopCategoryModel();
        $categories = $categoriesClass->getTree(false, 0);
        $catPicModel = new shopCategoryimagesModel();

        if(isset($categories) && !empty($categories)) {
            foreach ($categories as &$value) {
                $categoryImage = $catPicModel->getByField('category_id', $value['id']);
                $path = wa()->getDataUrl("categories/{$value['id']}/", true, 'shop');

                if ($categoryImage) {
                    $value['catClass'] = 'isPicture';
                    $value['checked'] = ($categoryImage['standard_out']) ? "checked" : "";
                    $value['picPath'] = $path . $categoryImage['id'] . "." .$categoryImage['ext'];
                    $value['featherlight'] = "data-featherlight='{$value['picPath']}'";
                } else {
                    $value['catClass'] = '';
                    $value['checked'] = '';
                    $value['picPath'] = wa()->getAppStaticUrl('shop') . 'plugins/categoryimages/img/no-image-pic.png';
                    $value['featherlight'] = "";
                }

                $hasChildren = $categoriesClass->getByField(array('parent_id' => $value['id']));
                if (isset($hasChildren) && !empty($hasChildren)) {
                    $value['arrow'] = '';
                } else {
                    $value['arrow'] = 'without-arrow';
                }
            }
            unset ($value);

            $this->view->assign(array(
                'categories' => $categories,
            ));
        }
    }
}