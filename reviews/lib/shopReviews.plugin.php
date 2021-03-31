<?php

/**
 * @author Плагины Вебасист <info@wa-apps.ru>
 * @link http://wa-apps.ru/
 */
class shopReviewsPlugin extends shopPlugin
{

    /**
     * @return string
     */
    public function frontendNav()
    {
        if ($this->getSettings('hook') == 'frontend_nav') {
            return $this->nav();
        }
    }

    /**
     * @return string
     */
    public function frontendNavAux()
    {
        if ($this->getSettings('hook') == 'frontend_nav_aux') {
            return $this->nav();
        }
    }

    protected function nav()
    {
        $view = wa()->getView();
        $title = $this->getSettings('title');
        if (!$title) {
            $title = _w('Reviews');
        }
        $view->assign('reviews_title', $title);
        if (($t_nav = $this->getSettings('template_nav')) !== null) {
            if ($t_nav) {
                return $view->fetch('string:'.$t_nav);
            } else {
                return;
            }
        } else {
            return $view->fetch($this->path.'/templates/frontendNav.html');
        }
    }

    public function backendCustomers($params)
    {
        $review_model = new shopReviewsModel();
        $count = $review_model->countAll();
        if (!$params) {
            return array(
                'sidebar_top_li' => '<li id="s-reviews"><span class="count">'.$count.'</span>
                <a href="#/reviews/"><i class="icon16 comments"></i>'._w('Reviews').'</a>
                </li><script src="'.$this->getPluginStaticUrl().'js/reviews.js?'.$this->getVersion().'"></script>'
            );
        }
    }

    public static function getRatings()
    {
        $model = new shopReviewsModel();
        $ratings = $model->getRatings();
        $result = array();
        $count = 0;
        $sum = 0;
        for ($i = 5; $i > 0; $i--) {
            if (!empty($ratings[$i])) {
                $count += $ratings[$i];
                $sum += $i * $ratings[$i];
                $result['ratings'][$i] = $ratings[$i];
            } else {
                $result['ratings'][$i] = 0;
            }
        }
        $result['count'] = $count;
        if ($count) {
            $result['rating'] = $sum / $count;
        }

        return $result;
    }

    public static function getReviews($limit = 5)
    {
        $model = new shopReviewsModel();
        $reviews = $model->getReviews(0, $limit);
        foreach ($reviews as &$r) {
            $r['name'] = htmlspecialchars($r['name']);
            $r['text'] = nl2br(htmlspecialchars($r['text']));
            if ($r['response']) {
                $r['response'] = nl2br($r['response']);
            }
        }
        unset($r);
        return $reviews;
    }

    public static function countReviews()
    {
        $model = new shopReviewsModel();
        return $model->countReviews();
    }

    public function saveSettings($settings = array())
    {
        if (isset($settings['template_reviews'])) {
            $path = wa('shop')->getDataPath('plugins/reviews/frontendReviews.html', false, 'shop', true);
            if ($settings['template_reviews']) {
                if (is_writable(dirname($path))) {
                    if (file_put_contents($path, $settings['template_reviews'])) {
                        $app_settings_model = new waAppSettingsModel();
                        if ($app_settings_model->get(array('shop', 'reviews'), 'template_reviews')) {
                            $app_settings_model->del(array('shop', 'reviews'), 'template_reviews');
                        }
                        unset($settings['template_reviews']);
                    }
                }
            } elseif (file_exists($path)) {
                try {
                    waFiles::delete($path);
                    unset($settings['template_reviews']);
                } catch (waDbException $e) {
                }
            }
        }
        parent::saveSettings($settings);
    }

    public function routing($route = array())
    {
        $url = $this->getSettings('url');
        if (!$url) {
            $url = 'reviews';
        }
        return array(
            $url.'/' => 'frontend/reviews',
        );
    }

    /**
     * @param array $route
     * @return array
     */
    public function sitemap($route)
    {
        if ($this->getSettings('sitemap')) {
            $url = wa()->getRouteUrl('shop/frontend/reviews', array(), true);
            return array(
                array(
                    'loc' => $url,
                    'changefreq' => waSitemapConfig::CHANGE_WEEKLY,
                    'priority' => (float)$this->getSettings('sitemap')
                )
            );
        }
    }
}
