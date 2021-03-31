<?php

class shopAutotagsBrandpageEngine extends shopAutotagsEngine {

    protected $type = 'brandpage';
    protected $name = 'Страница Бренда';
    protected $placeholders = array(
        'meta_title' => '{$brand.name} купить в интернет-магазине {$wa->shop->settings(\'name\')}',
        'meta_keywords' => '',
        'meta_description' => '',
    );

    private function getBrand($brand_url) {
        $feature_model = new shopFeatureModel();
        $key = array('shop', 'productbrands');
        $feature_id = wa()->getSetting('feature_id', '', $key);
        $feature = $feature_model->getById($feature_id);


        $values_model = $feature_model->getValuesModel($feature['type']);

        $brands_model = new shopProductbrandsModel();
        $brand = $brands_model->getByField('url', $brand_url);

        if (!$brand) {
            $value_id = $values_model->getValueId($feature['id'], $brand_url);
            if (!$value_id) {
                return false;
            }
            $brand = $brands_model->getBrand($value_id);
        } else {
            $brand['params'] = shopProductbrandsModel::getParams($brand['params']);
            // check feature value exists
            if (!$values_model->getById($brand['id'])) {
                $brands_model->updateById($brand['id'], array('url' => ''));
                return false;
            }
        }
        return $brand;
    }

    public function hookFrontendFooter() {
        if (($brand_url = waRequest::param('brand')) && class_exists('shopProductbrandsPlugin')) {
            $brand = $this->getBrand($brand_url);
            if ($brand) {
                $vars = array(
                    'brand' => $brand,
                );
                $meta_tags = $this->getTemplates($vars);
                $this->setMeta($meta_tags, $vars, $tag);
            }
        }
    }

}
