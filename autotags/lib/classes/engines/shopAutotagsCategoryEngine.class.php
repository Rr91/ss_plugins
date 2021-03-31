<?php

class shopAutotagsCategoryEngine extends shopAutotagsEngine
{

    protected $type = 'category';
    protected $name = 'Страница категории';
    protected $is_category_settings = 1;
    protected $placeholders = array(
        'meta_title' => '{category.name} купить в интернет-магазине {$wa->shop->settings(\'name\')}',
        'meta_keywords' => '',
        'meta_description' => '',
        'description' => 'Если описание категории не заполнено, то оно будет формироваться по данному шаблону',
    );

    public function hookFrontendCategory($category)
    {
        $category_model = new shopCategoryModel();
        $path = $category_model->getPath($category['id']);
        $parent_categories = array_reverse(array_values($path));
        $parent_category_names = array();
        if ($parent_categories) {
            foreach ($parent_categories as $item) {
                $parent_category_names[] = $item['name'];
            }
        }

        $range = $this->getPriceRange($category);

        $vars = array(
            'category' => $category,
            'page_number' => waRequest::get('page'),
            'parent_categories' => $parent_categories,
            'parent_category_names' => $parent_category_names,
            'parent_category_name' => end($parent_category_names),
            'root_category_name' => reset($parent_category_names),
            'min_price' => $range['min'],
            'max_price' => $range['max'],
        );
        $meta_tags = $this->getTemplates($vars);

        $this->setMeta($meta_tags, $vars, $category);

        if ((empty($category['description']) || !empty($meta_tags['override'])) && !empty($meta_tags['description'])) {
            $view = wa()->getView();
            $category['description'] = $view->fetch('string:' . $meta_tags['description']);
            $view->assign('category', $category);
        }
    }

    protected function getPriceRange($category)
    {
        $collection = new shopProductsCollection('category/' . $category['id']);
        $collection->addWhere('p.min_price > 0');
        $sql = $collection->getSQL();
        $sql = "SELECT MIN(p.min_price) min, MAX(p.max_price) max ".$sql;
        $model = new waModel();
        $data = $model->query($sql)->fetch();

        return array(
            'min' => (double)(isset($data['min']) ? $data['min'] : 0),
            'max' => (double)(isset($data['max']) ? $data['max'] : 0)
        );
    }

    public function hookBackendCategoryDialog($category)
    {
        return '<div class="field">
                    <div class="name">
                        <strong>SEO Название</strong>
                    </div>
                    <div class="value">
                        <input class="large long s-full-width-input" placeholder="' . $category['name'] . '"  type="text" name="autotags_seo_name" value="' . $category['autotags_seo_name'] . '" />
                    </div>
                </div>';
    }

    public function hookCategorySave($category)
    {
        $category_model = new shopCategoryModel();
        if ($autotags_seo_name = waRequest::post('autotags_seo_name')) {
            $category_model->updateById($category['id'], array('autotags_seo_name' => $autotags_seo_name));
        }

    }

    public function hookInstall()
    {
        try {
            $model = new waModel();
            $sql = 'ALTER TABLE `shop_category` ADD `autotags_seo_name` VARCHAR( 255 ) NULL AFTER `name`';
            $model->query($sql);
        } catch (waDbException $ex) {

        }
    }

    public function hookUninstall()
    {
        try {
            $model = new waModel();
            $sql = 'ALTER TABLE `shop_category` DROP `autotags_seo_name`';
            $model->query($sql);
        } catch (waDbException $ex) {

        }
    }

}
