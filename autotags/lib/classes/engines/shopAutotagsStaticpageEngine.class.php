<?php

class shopAutotagsStaticpageEngine extends shopAutotagsEngine {

    protected $type = 'staticpage';
    protected $name = 'Статическая страница';
    protected $placeholders = array(
        'meta_title' => '{$page.name} | интернет-магазине {$wa->shop->settings(\'name\')}',
        'meta_keywords' => '',
        'meta_description' => '',
    );

    public function hookFrontendFooter() {
        if (waRequest::param('page_id')) {
            $view = wa()->getView();
            $page = $view->getVars('page');
            $vars = array(
                'page' => $page,
            );
            $meta_tags = $this->getTemplates($vars);
            $this->setMeta($meta_tags, $vars, $page);
        }
    }

}
