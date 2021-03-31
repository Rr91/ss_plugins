<?php

class shopReviewsPluginFrontendReviewsAction extends waViewAction
{
    /**
     * @var shopReviewsModel
     */
    protected $model;
    protected $response = array();
    protected $errors = array();

    public function execute()
    {
        if (!waRequest::isXMLHttpRequest() && !(waRequest::method() == 'post')) {
            $this->setLayout(new shopFrontendLayout());
        }

        $this->model = new shopReviewsModel();

        if (waRequest::method() == 'post') {
            $this->add();
        }

        $limit = $this->getSetting('records_per_page', 20);
        $page = waRequest::get('page');
        if (!$page) {
            $page = 1;
        }

        $offset = ($page - 1) * $limit;
        $reviews = $this->model->getReviews($offset, $limit);
        foreach ($reviews as &$r) {
            $r['name'] = htmlspecialchars($r['name']);
            $r['text'] = nl2br(htmlspecialchars($r['text']));
            if ($r['response']) {
                $r['response'] = nl2br($r['response']);
            }
        }
        unset($r);
        $count = $this->model->countReviews();
        $pages_count = ceil($count / $limit);

        /**
         * @var shopReviewsPlugin $plugin
         */
        $plugin = wa('shop')->getPlugin('reviews');


        $this->view->assign('reviews_settings', wa('shop')->getPlugin('reviews')->getSettings());
        $this->view->assign('reviews_count', $count);
        $this->view->assign('pages_count', $pages_count);
        $this->view->assign('reviews', $reviews);
        $this->view->assign('errors', $this->errors);
        $this->view->assign('require_authorization', $this->getSetting('require_authorization'));
        $this->view->assign('require_captcha', $this->getSetting('require_captcha', 1));
        $this->view->assign('enable_rating', $plugin->getSettings('enable_rating'));
        $this->view->assign('enable_response_info', $plugin->getSettings('enable_response_info'));

        $this->view->assign('wa_active_theme_path', $this->getTheme()->path);
    }

    protected function getSetting($name, $default = null)
    {
        return wa()->getSetting($name, $default, array('shop', 'reviews'));
    }

    protected function add()
    {
        $data = array();
        if (!$this->getUser()->isAuth()) {
            $data['name'] = waRequest::post('name');
            if (!$data['name']) {
                $this->errors['name'] = _ws('Required');
            }
            $email = waRequest::post('email');
            if ($email) {
                $email_validator = new waEmailValidator(array('required' => true));
                if (!$email_validator->isValid($email)) {
                    $this->errors['email'] = implode(', ', $email_validator->getErrors());
                }
                $data['email'] = $email;
            }
        } else {
            $data['contact_id'] = $this->getUser()->getId();
            $data['name'] = $this->getUser()->getName();
        }
        $data['text'] = trim(waRequest::post('text'));
        $data['rating'] = waRequest::post('rating', 0, waRequest::TYPE_INT);
        if ($data['rating'] > 5) {
            $data['rating'] = 5;
        }
        if (!$data['text']) {
            $this->errors['text'] = _ws('Required');
        }
        if (!$this->getUser()->isAuth() && $this->getSetting('require_captcha', 1) && !wa()->getCaptcha()->isValid()) {
            $this->errors['captcha'] = _ws('Invalid captcha');
        }

        if (!$this->errors) {
            $data['datetime'] = date('Y-m-d H:i:s');
            $data['status'] = $this->getSetting('premoderation') ? 0 : 1;
            if ($ip = waRequest::getIp()) {
                $ip = ip2long($ip);
                if ($ip > 2147483647) {
                    $ip -= 4294967296;
                }
                $data['ip'] = $ip;
            }
            $id = $this->model->insert($data);

            $image_extensions = array('jpg', 'jpeg', 'png', 'gif');
            $image = waRequest::file('image');
            if ($image->uploaded() && in_array(strtolower($image->extension), $image_extensions)) {
                try {
                    $image->waImage();

                    $data['image'] = '.' . $image->extension;
                    $path = wa()->getDataPath('reviews/', true, 'shop');
                    $image->moveTo($path, $id . $data['image']);
                    $this->model->updateById($id, array('image' => $data['image']));
                } catch (waException $e) {
                }
            }
            // upload image
            $images = waRequest::file('images');
            $data_images = array();
            foreach ($images as $i => $image) {
                if ($image->uploaded() && in_array(strtolower($image->extension), $image_extensions)) {
                    try {
                        $wa_image = $image->waImage();
                        $wa_image_save = false;
                        $image_path = time() . $i . '.' . $image->extension;
                        $data_images[] = $image_path;
                        $path = wa()->getDataPath('reviews/', true, 'shop');
                        if (in_array(strtolower($image->extension), array('jpg', 'jpeg')) && function_exists('exif_read_data')) {
                            $exif = @exif_read_data($image->tmp_name);
                            if (!empty($exif) && !empty($exif['Orientation'])) {
                                switch($exif['Orientation']) {
                                    case 3:
                                        $wa_image->rotate(180);
                                        $wa_image_save = true;
                                        break;
                                    case 6:
                                        $wa_image->rotate(90);
                                        $wa_image_save = true;
                                        break;
                                    case 8:
                                        $wa_image->rotate(-90);
                                        $wa_image_save = true;
                                        break;
                                }
                            }
                        }
                        if ($wa_image_save) {
                            $wa_image->save($path.$id . '_' . $image_path, 90);
                        } else {
                            $image->moveTo($path, $id . '_' . $image_path);
                        }
                        $this->response['images'][] =
                            wa()->getDataUrl('reviews/', true, 'shop') .
                            $id . '_' . $image_path;
                    } catch (waException $e) {
                    }
                }
            }
            if ($data_images) {
                $data['images'] = implode(';', $data_images);
                $this->model->updateById($id, array('images' => $data['images']));
            }

            if ($this->getSetting('premoderation')) {
                $reviews = wa()->getStorage()->get('shop/reviews');
                if ($reviews) {
                    $reviews .= ',';
                }
                $reviews .= $id;
                wa()->getStorage()->set('shop/reviews', $reviews);
            }

            $plugin = wa('shop')->getPlugin('reviews');
            $email = $plugin->getSettings('email');
            if ($email) {
                $subject = _wp('New review');
                $url = wa('shop')->getRouteUrl('shop/frontend/reviews', array(), true);
                $body = _wp("The new review has been written in your store:");
                $body .= <<<HTML
<br>
<a href="{$url}">{$url}</a><br><br>
<b>{$data['name']}</b>
<p>{$data['text']}</p>
HTML;
                $message = new waMailMessage($subject, $body);
                $message->setTo($email);
                $message->send();
            }
        }
    }

    public function display($clear_assign = true)
    {
        $custom_template = wa('shop')->getDataPath('plugins/reviews/frontendReviews.html', false, 'shop', false);
        if (file_exists($custom_template)) {
            $template = 'file:'.$custom_template;
        } elseif ($t = wa()->getSetting('template_reviews', '', array('shop', 'reviews'))) {
            $template = 'string:'.$t;
        } else {
            $template = 'file:'.wa()->getAppPath('plugins/reviews/templates/', 'shop').'frontendReviews.html';
        }
        $this->execute();
        if (waRequest::isXMLHttpRequest() || waRequest::method() == 'post') {
            if (!waRequest::isXMLHttpRequest()) {
                wa()->getResponse()->addHeader('Content-Type', 'text/plain');
            }
            if ($this->errors) {
                return json_encode(array('status' => 'fail', 'errors' => $this->errors));
            } else {
                return json_encode(array('status' => 'ok', 'data' => $this->view->fetch($template)));
            }
        } else {
            /**
             * @var shopReviewsPLugin $plugin
             */
            $plugin = wa('shop')->getPlugin('reviews');
            $title = $plugin->getSettings('title');
            if (!$title) {
                $title = _w('Reviews');
            }
            $this->view->assign('page', array(
                'id' => 'reviews',
                'title' => $title,
                'name' => $title,
                'content' => $this->view->fetch($template)
            ));

            // set meta tags
            $meta_title = $plugin->getSettings('meta_title');
            if ($meta_title) {
                $meta_title = $this->pageMask($meta_title);
            } else {
                $meta_title = $title;
            }
            $this->getResponse()->setTitle($meta_title);
            foreach (array('description', 'keywords') as $k) {
                if ($v = $plugin->getSettings('meta_'.$k)) {
                    $v = $this->pageMask($v);
                    $this->getResponse()->setMeta($k, $v);
                }
            }
            /**
             * @event frontend_nav
             * @return array[string]string $return[%plugin_id%] html output for navigation section
             */
            $this->view->assign('frontend_nav', wa()->event('frontend_nav'));
            $this->view->assign('frontend_nav_aux', wa()->event('frontend_nav_aux'));

            $this->setThemeTemplate('page.html');

            waSystem::popActivePlugin();
            return $this->view->fetch($this->getTemplate());
        }
    }

    protected function pageMask($mask)
    {
        if (strpos($mask, '%page%') !== false) {
            $page = waRequest::get('page', 1, 'int');
            if (preg_match('/\[[^\]]*?%page%[^\]]*?\]/uis', $mask)) {
                $mask = preg_replace('/\[([^\]]*?)%page%([^\]]*?)\]/uis', $page > 1 ? '$1%page%$2' : '', $mask);
            }
            $mask = str_replace('%page%', $page > 0 ? $page : '', $mask);
        }
        return $mask;
    }
}
