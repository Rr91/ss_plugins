<?php

class shopAutotagsProductEngine extends shopAutotagsEngine
{
    protected $type = 'product';

    protected $name = 'Страница товара';

    protected $is_category_settings = 1;

    protected $placeholders = [
        'meta_title' => '{$product.name}, {$sku.name} купить по цене {shop_currency($product.price)} в интернет-магазине {$wa->shop->settings(\'name\')}',
        'meta_keywords' => '',
        'meta_description' => '',
        'description' => 'Если описание товара не заполнено, то оно будет формироваться по данному шаблону',
    ];

    public function hookFrontendProduct($product)
    {
        $sku_id = $product['sku_id'];
        $sku = $product['skus'][$sku_id];

        $category_model = new shopCategoryModel;
        $category = $category_model->getById($product['category_id']);

        $path = $category_model->getPath($category['id']);
        $parent_categories = array_reverse(array_values($path));
        $parent_categories[] = $category;

        $parent_category_names = [];
        if ($parent_categories) {
            foreach ($parent_categories as $item) {
                $parent_category_names[] = $item['name'];
            }
        }

        $is_reviews_page = false;
        if (strpos(wa()->getRouting()->getCurrentUrl(), '/reviews/') !== false) {
            $is_reviews_page = true;
        }

        $vars = [
            'product' => $product,
            'sku' => $sku,
            'parent_category' => end($parent_categories),
            'root_category' => reset($parent_categories),
            'parent_categories' => $parent_categories,
            'parent_category_names' => $parent_category_names,
            'is_reviews_page' => $is_reviews_page,
        ];

        if (waRequest::param('page_url') && stripos(waRequest::param('action'), 'seopage') == false) {
            $page_model = new shopProductPagesModel;
            $page =
                $page_model->getByField(['product_id' => $product['id'], 'url' => waRequest::param('page_url')]);
            $vars['page'] = $page;
        }

        $meta_tags = $this->getTemplates($vars);
        $this->setMeta($meta_tags, $vars, $product);

        $autotags_product_model = new shopAutotagsPluginProductModel;
        $autotags_product =
            $autotags_product_model->getByField(['route_hash' => shopAutotags::getCurrentRouteHash(),
                'product_id' => $product['id']]);

        if (empty($autotags_product['description'])) {
            $autotags_product =
                $autotags_product_model->getByField(['route_hash' => 0, 'product_id' => $product['id']]);
        }

        if (!empty($autotags_product['description'])) {
            $view = wa()->getView();
            $product['description'] = $view->fetch('string:' . $autotags_product['description']);
            $view->assign('product', $product);
        } elseif ((empty($product['description']) || !empty($meta_tags['override'])) && !empty($meta_tags['description'])) {
            $view = wa()->getView();
            $product['description'] = $view->fetch('string:' . $meta_tags['description']);
            $view->assign('product', $product);
        }
    }

    public function hookBackendProductEdit($product)
    {
        $template =
            wa()->getAppPath('plugins/autotags/templates/engines/BackendProductEdit/' . $this->type . 'ProductEditFields.html',
                'shop');
        if (file_exists($template)) {
            $view = wa()->getView();
            $view->assign([
                'product' => $product,
                'route_hashs' => shopAutotags::getRouteHashs(),
            ]);
            $html = $view->fetch($template);

            return ['basics' => $html];
        }
    }

    public function hookProductSave($params)
    {
        if ($autotags_product = waRequest::post('autotags_product')) {
            $route_hash = waRequest::post('route_hash');
            $data = $params['data'];
            $key = ['route_hash' => $route_hash, 'product_id' => $data['id']];

            $autotags_product_model = new shopAutotagsPluginProductModel;
            if ($autotags_product_model->getByField($key)) {
                $autotags_product_model->updateByField($key, $autotags_product);
            } else {
                $data = array_merge($key, $autotags_product);
                $autotags_product_model->insert($data);
            }
        }
    }

    public function hookInstall()
    {
        try {
            $model = new waModel;
            $sql = 'ALTER TABLE `shop_product` ADD `autotags_seo_name` VARCHAR( 255 ) NULL AFTER `name`';
            $model->query($sql);
        } catch (waDbException $ex) {

        }
    }

    public function hookUninstall()
    {
        try {
            $model = new waModel;
            $sql = 'ALTER TABLE `shop_product` DROP `autotags_seo_name`';
            $model->query($sql);
        } catch (waDbException $ex) {

        }
    }
}
