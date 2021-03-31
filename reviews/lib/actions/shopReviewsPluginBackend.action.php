<?php

class shopReviewsPluginBackendAction extends waViewAction
{
    public function execute()
    {
        $page = waRequest::get('page');
        if (!$page) {
            $page = 1;
        }

        $limit = 50;

        $model = new shopReviewsModel();

        $offset = ($page - 1) * $limit;
        $reviews = $model->getReviews($offset, $limit);
        $count = $model->countAll();
        $pages_count = ceil($count / $limit);

        $this->view->assign('settings', wa('shop')->getPlugin('reviews')->getSettings());
        $this->view->assign('page', $page);
        $this->view->assign('pages_count', $pages_count);
        $this->view->assign('reviews', $reviews);
    }
}