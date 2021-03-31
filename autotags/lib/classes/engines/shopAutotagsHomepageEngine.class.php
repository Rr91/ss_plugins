<?php

class shopAutotagsHomepageEngine extends shopAutotagsEngine {

    protected $type = 'homepage';
    protected $name = 'Главная страница';
    protected $placeholders = array(
        'meta_title' => '{$wa->shop->settings(\'name\')} интернет-магазин товаров для сада',
        'meta_keywords' => '',
        'meta_description' => '',
    );

    public function hookFrontendHomepage() {
        $meta_tags = $this->getTemplates();
        $this->setMeta($meta_tags);
    }

}
