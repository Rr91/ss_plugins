<?php

class shopAutotagsTagpageEngine extends shopAutotagsEngine {

    protected $type = 'tagpage';
    protected $name = 'Страница тега';
    protected $placeholders = array(
        'meta_title' => '{$tag} купить в интернет-магазине {$wa->shop->settings(\'name\')}',
        'meta_keywords' => '',
        'meta_description' => '',
    );

    public function hookFrontendFooter() {
        if ($tag = waRequest::param('tag')) {
            $vars = array(
                'tag' => $tag,
                'page_number' => waRequest::get('page'),
            );
            $meta_tags = $this->getTemplates($vars);
            $this->setMeta($meta_tags, $vars, $tag);
        }
    }

}
