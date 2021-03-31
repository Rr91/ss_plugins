<?php

class shopReviewsPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        /**
         * @var shopReviewsPlugin $plugin
         */
        $plugin = wa('shop')->getPlugin('reviews');
        $settings = $plugin->getSettings();
        $this->view->assign('settings', $settings);

        $path = wa()->getAppPath('plugins/reviews/templates/', 'shop');
        if (isset($settings['template_nav'])) {
            $this->view->assign('template_nav', $settings['template_nav']);
        } else {
            $this->view->assign('template_nav', file_get_contents($path.'frontendNav.html'));
        }

        $custom_template = wa('shop')->getDataPath('plugins/reviews/frontendReviews.html', false, 'shop', false);
        if (file_exists($custom_template)) {
            $this->view->assign('template_reviews', file_get_contents($custom_template));
        } elseif (!empty($settings['template_reviews'])) {
            $this->view->assign('template_reviews', $settings['template_reviews']);
        } else {
            $this->view->assign('template_reviews', file_get_contents($path.'frontendReviews.html'));
        }
        $this->view->assign('frontend_url', wa()->getRouteUrl('shop/frontend/reviews', array(), true));
    }
}
