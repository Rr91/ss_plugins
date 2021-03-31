<?php

if (!function_exists('boolval')) {
    function boolval($val) {
        return (bool) $val;
    }
}
class shopSeoreviewsPlugin extends shopPlugin
{
    const APP = 'shop';
    const PLUGIN_ID = 'seoreviews';
    protected static $search_types = array(
        'all' => 'Искать среди товаров категории и всех подкатегорий',
        'category' => 'Искать только среди товаров категории',
        'page' => 'Искать только среди товаров показанных на странице',
        /* 'custom' => 'Искать только среди переданных типов товаров или переданных товаров',  */
    );
    protected static $sorts = array(
        'desc' => 'Новые отзывы сверху',
        'asc' =>  'Старые отзывы сверху',
        'max_reviews' => 'Первыми показывать отзывы продуктов с наибольшим количеством отзывов',
        'best_reviews' => 'Первыми показывать отзывы продуктов с лучшим рейтингом',
    );
    protected $default_settings = array(

            'frontend_category' => '1',
            'limit' => '5',
            'min_view_reviews' => '2',

            'search_type' => 'all',
            'sort' => 'max_reviews',

            'max_rate' => '5',//+++
            'min_rate' => '1',//+++
            'min_chars' => '10',

    );

    protected static $view_reviews = array();
    protected static $view_products = array();
    protected static $reviews = array();
    protected static $init_scripts = false;
    protected static $category_id = null;
    protected static $frontend_category = false;

    protected static $templates = null;

    public function frontendCategory($category) {
        if(array_key_exists('id', $category) && self::isOn()) {
            self::$frontend_category = true;
            self::$category_id = intval($category['id']);
            // Если хук не включен, уходим
            $hook = $this->getSettings('frontend_category');
            if(empty($hook)) {
                return null;
            }
            // Показываем отзывы категории в хуке
            return self::getReviewsHtml('horizontal');
        }
        return null;
    }
    public function frontendProducts($data = array()) {
        if(array_key_exists('products', $data) && self::isOn()) {
            shopSeoreviewsPluginProductsPool::getInstance($this)->addProducts($data['products']);
        }
    }
    public function frontendProduct($data) {
        if(self::isOn() && $this->getSettings('frontend_product')==1 && isset($data['id']) && !empty($data['id']))  {
            $view_type = $this->getSettings('frontend_product_search_type');
            if($view_type == 'category') {
                $options = array('search_type' => 'custom', 'category_id' => $data['category_id']);
            } else {
                $options = array('search_type' => 'custom', 'types' => $data['type_id']);
            }
            return array('block' => self::getReviewsHtml($options));
        }
        return null;
    }
    public function frontendSearch() {
        if(self::isOn() && $this->getSettings('frontend_search')==1)  {
           $options = array('search_type' => 'page',  'min_view_reviews' => '1');
            return  self::getReviewsHtml($options);
        }
        return null;
    }
    public static function isOn() {
        $info = wa(self::APP)->getConfig()->getPluginInfo(self::PLUGIN_ID);
        if(!empty($info)) {
            $plugin = self::getInstance();
            $status = $plugin->getSettings('status');
            return (!empty($status));
        }
        return false;

    }
    public static function getReviews($options = array()) {
        if(!self::isOn()) {
            return array();
        }
        $plugin = self::getInstance();
        $options = $plugin->prepareReviewsOptions($options);

        /* init category data */
        $category_model = new shopCategoryModel();

        $select = $where = $join =  array(); 
        /* Поиск среди категорий  */
        $category_types = array('all','category', 'custom' );
        if(array_key_exists('category_id', $options)
            && intval($options['category_id']) > 0
            && array_key_exists('search_type', $options)
            && in_array($options['search_type'],  $category_types)
        )  {
            $category_data = $category_model->getById($options['category_id']);
            if(empty($category_data) && intval($options['category_id']) > 0) {
                return array();
            } elseif(intval($options['category_id']) == 0) {
                $category_data = array(
                    'left_key' => 0,
                    'right_key' => (int)$category_model->select('MAX(right_key)')->fetchField()
                );
            }

            $category_type = isset($category_data['type']) ? $category_data['type']:0;
            if($category_type == 1) {
                $products_collection = new shopProductsCollection('category/'.$category_data['id']);
               $products_collection->getSQL();
                $join[] = ' JOIN (SELECT p.id '.$products_collection->getSQL().') p ON sp.product_id = p.id ';
            } else {
                $join[] = ' JOIN shop_category_products sr_cp1 ON sp.product_id = sr_cp1.product_id ';
                if($options['search_type'] == 'all') {
                    $left_key = intval($category_data['left_key']);
                    $right_key = intval($category_data['right_key']);
                    $category_where = ' sr_cp1.category_id = '.$category_model->escape($options['category_id'], 'int').'';
                    $category_where .= '   OR   sr_cp1.category_id IN (SELECT id  FROM shop_category sc WHERE sc.left_key >  '
                        .$category_model->escape($left_key, 'int').
                        ' AND sc.right_key <  '.$category_model->escape($right_key, 'int').')';
                    $where[] = '('.$category_where.')';

                } else {
                    $category_where = ' sr_cp1.category_id = '.$category_model->escape($options['category_id'], 'int').'';
                    $where[] = '('.$category_where.')';
                    $join[] = ' JOIN shop_category_products sr_cp1 ON sp.product_id = sr_cp1.product_id ';

                }
            }
        }
        else  if( isset($options['search_type']) && $options['search_type'] == 'custom') {
            /* поиск по продуктам и типам ниже */
        }
        /* Поиск среди продуктов страницы */
        else {
            /* Поиск среди продуктов страницы */
            $products =  shopSeoreviewsPluginProductsPool::getInstance($plugin)->getKeys();
            if(!empty($products)) {
                $options['products'] = $products;
            }
        }

        /* поиск по продуктам и типам  */
        if(array_key_exists('products', $options) && !empty($options['products']) &&  is_array($options['products'])) {
            $where[] = 'sp.product_id IN ('.implode(',',$options['products']).')';
        }
        if(array_key_exists('types',$options) &&  !empty($options['types'])) {
            $join[] = ' JOIN shop_product sh_p ON sh_p.id  = sp.product_id ';
            if(is_array($options['types'])) {
                $where[] = 'sh_p.type_id IN ('.implode(',',$options['types']).')';
            } elseif(wa_is_int($options['types'])) {
                $where[] = 'sh_p.type_id  = '.$category_model->escape($options['types'], 'int').'';
            }
        }
        if(array_key_exists('exclude_products', $options) && $options['exclude_products'] && !empty(self::$view_products) && is_array(self::$view_products)) {
            $where[] = 'sp.product_id  NOT IN ('.implode(',',self::$view_products).')';
        }

        /* Поиск по рейтингу */
        if($options['min_rate'] && $options['max_rate']) {
            if($options['min_rate'] == $options['max_rate']) {
                $where [] = 'sp.rate = '.$category_model->escape($options['min_rate'],'int').'';
            } else {
                $where [] = 'sp.rate >= '.$category_model->escape($options['min_rate'],'int').'';
                $where [] = 'sp.rate <= '.$category_model->escape($options['max_rate'],'int').'';
            }
        } else {
            if($options['min_rate']) {
                $where [] = 'sp.rate >= '.$category_model->escape($options['min_rate'],'int').'';
            }
            if($options['max_rate']) {
                $where [] = 'sp.rate <= '.$category_model->escape($options['max_rate'],'int').'';
            }
        }
        /* Минимальное количество символов */
        if($options['min_chars']) {
            $where [] = 'char_length(sp.text) >= '.$category_model->escape($options['min_chars'],'int').'';
        }

        /* Дополнительные данные категорий для формирорвания ссылки на продкут */
        $join_category = '';
        if (waRequest::param('url_type') == 2) {
            $join_category = ' JOIN shop_category category ON product_data.category_id = category.id ';
            $select[] = ' category.full_url product_category_url ';
        }

        /* Исключаем уже показанные отзывы */
        if(!empty(self::$view_reviews)) {
            $where [] = 'sp.id NOT IN ('.implode(',',self::$view_reviews).')';
        }
        /* Готовим переменные запроса */
        if(!empty($select)) {
            $select = ', '.trim(implode(',', $select),',');
        } else {
            $select = '';
        }
        if(!empty($join)) {
            $join = implode(' ', $join);
        } else {
            $join = '';
        }

        $order = ' spr.datetime DESC ';
        $sub_select = '';
        $sub_order = ' sp.datetime DESC ';
        if($options['sort'] == 'asc') {
            $sub_order = ' sp.datetime ASC ';
            $order = ' spr.datetime ASC ';
        } elseif ($options['sort'] == 'best_reviews') {
            $sub_select = ', COUNT(sp.id) product_count_reviews, SUM(sp.rate) as product_summ_rate, AVG(sp.rate) as product_rate';
            $sub_order = ' product_rate, product_count_reviews DESC';
            $order = 'reviews.product_rate, spr.datetime  DESC ';

        }  elseif($options['sort'] == 'max_reviews') {
            $sub_select = ', COUNT(sp.id) product_count_reviews, SUM(sp.rate) as product_summ_rate, AVG(sp.rate) as product_rate';
            $sub_order = ' product_count_reviews, sp.datetime DESC';
            $order = 'reviews.product_count_reviews DESC ';
        }
        $where[] = ' sp.parent_id = 0 ';
        $where[] = " sp.status  = '".shopProductReviewsModel::STATUS_PUBLISHED."' ";


        $sql = "SELECT spr.* ,  
                          product_data.name product_name,    
                          product_data.url product_url,
                          product_data.image_id product_image_id,
                          product_data.image_filename product_image_filename,
                          product_data.ext product_ext {$select}
                      FROM shop_product_reviews spr
                      INNER JOIN (
                        SELECT sp.id, MAX(sp.id) max_id, sp.product_id {$sub_select}
                        FROM shop_product_reviews sp
                        {$join}
                        WHERE ".implode(" AND ", $where)."
                        GROUP BY sp.product_id
                        ORDER BY {$sub_order}
                     ) reviews on spr.product_id = reviews.product_id
                     JOIN shop_product product_data ON spr.product_id = product_data.id {$join_category}
                    WHERE  spr.id = reviews.id
                    ORDER BY {$order}
                    LIMIT 0,{$options['limit']}";

        $contact_ids = array();
        $reviews = array();
        foreach($category_model->query($sql)->fetchAll('id') as $id => $review) {
            $id  = intval($id); // паранойя
            // Пишем в исключение выводимые отзывы
            self::$view_reviews [$id] = $id;
            self::$view_products [$review['product_id']] = $review['product_id'];
            /* Экранируем для показа */
            $fields = array(
                'title',
                'name',
                'text',
                'email',
                'site',
                'auth_provider',
                'product_name',
                'product_url',
                'product_category_url',
            ) ;
            foreach ($fields as $field)  {
                if(array_key_exists($field, $review)) {
                    if($options['use_html'] && $field == 'text') {
                        continue;
                    }
                    $review[$field] = htmlspecialchars($review[$field]);
                }
            }
            /* Данные продукта */
            $product = array(
                'id' => $review['product_id'],
                'name' => $review['product_name'],
                'url' =>  $review['product_url'],
                'image_id' => $review['product_image_id'],
                'image_filename' => $review['product_image_filename'],
                'ext' => $review['product_ext']
            );
            if(array_key_exists('product_category_url', $review))  {
                $product['category_url'] = $review['product_category_url'];
            }

            $review['product'] = $product;
            if ($review['contact_id']) {
                $contact_ids[] = $review['contact_id'];
            }

            unset($review['product_name'],
                $review['product_url'],
                $review['product_image_id'],
                $review['product_image_filename'],
                $review['product_ext'],
                $review['product_category_url']
            );
            $reviews[$id] = $review;
        }

        $contact_ids = array_unique($contact_ids);
        $contacts = shopProductReviewsModel::getAuthorInfo($contact_ids);
        foreach ($contacts as &$contact) {
            $contact = array_map('htmlspecialchars', $contact);
        }
        unset($contact);

        foreach ($reviews as &$item) {
            $author = array(
                'name' => htmlspecialchars($item['name']),
                'email' => htmlspecialchars($item['email']),
                'site' => htmlspecialchars($item['site'])
            );
            $item['author'] = array_merge(
                $author,
                isset($contacts[$item['contact_id']]) ? $contacts[$item['contact_id']] : array()
            );
        }
        unset($item);
        return $reviews;
    }
    public static function getReviewsHtml($options = array()) {
        if(!self::isOn()) {
            return '';
        }
        $plugin = self::getInstance();
        if(!is_array($options)) {
            $options = array();
        }
        $view = $plugin->getView();
        $wa_view = wa()->getView();
        $view->assign($wa_view->getVars());
        // Ставим шаблон
        if(!array_key_exists('template', $options)) {
            $options['template'] = 'horizontal';
        }
        if(array_key_exists('header', $options))  {
            $options['header'] = htmlspecialchars($options['header']);
        } elseif ($plugin->getSettings('header')!==null) {
            $options['header'] = htmlspecialchars($plugin->getSettings('header'));
        } else {
            $options['header'] =  'Отзывы покупателей';
        }
        $view->assign('header',  $options['header']);
        // Минимальное количество отзывов
        if(array_key_exists('min_view_reviews', $options)) {
            $options['min_view_reviews'] = intval($options['min_view_reviews']);
        } else{
            $options['min_view_reviews'] = intval($plugin->getSettings('min_view_reviews'));
        }
        if($options['min_view_reviews'] < 1 ||  $options['min_view_reviews']> 50) {
            $options['min_view_reviews'] = 1;
        }
        $view->assign('min_view_reviews',  $options['min_view_reviews']);
        // Обрезка текста отзыва
        if(array_key_exists('review_title_width', $options)) {
            $options['review_title_width'] = intval($options['review_title_width']);
        } else{
            $options['review_title_width'] = intval($plugin->getSettings('review_title_width'));
        }
        if($options['review_title_width'] < 10 ||  $options['min_view_reviews'] > 120) {
            $options['review_title_width'] = 50;
        }
        $view->assign('review_title_width',  $options['review_title_width']);
        // Обрезка текста отзыва
        if(array_key_exists('review_text_width', $options)) {
            $options['review_text_width'] = intval($options['review_text_width']);
        } else{
            $options['review_text_width'] = intval($plugin->getSettings('review_text_width'));
        }
        if($options['review_text_width'] < 20) {
            $options['review_text_width'] = 180;
        }
        $view->assign('review_text_width',  $options['review_text_width']);
        // Включение слайдера
        if(array_key_exists('slider', $options) &&  empty($options['slider'])) {
            $options['slider'] = false;
        } else{
            $options['slider'] = true;
        }
        $view->assign('slider',   $options['slider']);
        // Прокрутка мышью
        if(array_key_exists('mousewheel', $options)) {
            $options['mousewheel'] = (bool)$options['mousewheel'];
        } else{
            $options['mousewheel'] = (bool)$plugin->getSettings('mousewheel');
        }
        $view->assign('mousewheel',   $options['mousewheel']);


        $view->assign('reviews', self::getReviews($options));

        $html = $plugin->getScripts();
        $html .= $view->fetch(self::getTemplate($options['template']));
        return $html;
    }

    public static function getSearchTypes() {
        return self::$search_types;
    }
    public static function getSearchSorts() {
        return self::$sorts;
    }

    public static function getPluginUrlStatic($absolute = false) {
        return wa()->getAppStaticUrl(self::APP, $absolute).'plugins/'.self::PLUGIN_ID.'/';
    }

    /**
     * @return shopSeoreviewsPlugin
     */
    protected static function getInstance() {
        return wa('shop')->getPlugin(self::PLUGIN_ID);
    }
    protected function getView(){
        return new waSmarty3View(wa('shop'));
    }
    protected static function getTemplate($template_name = 'horizontal') {
        if(self::$templates == null) {
            self::$templates = new shopSeoreviewsPluginTemplates(self::getInstance()->getSettings());
        }
        return   self::$templates->getTemplate($template_name);

    }

    protected function prepareReviewsOptions($options = array()){
        /* Validate options */
        if(!is_array($options)) {
            $options = array();
        }
        /* LIMIT */
        if(array_key_exists('limit', $options)) {
            $options['limit'] = intval($options['limit']);
        } else {
            $options['limit'] = intval($this->getSettings('limit'));
        }
        $options['limit'] = ($options['limit'] < 1 || $options['limit'] > 500)? 10 : $options['limit'];

        /* Search type */
        if(!array_key_exists('search_type', $options)) {
            $options['search_type'] = $this->getSettings('search_type');
        }
        if(!array_key_exists($options['search_type'], self::getSearchTypes()) && $options['search_type']!= 'custom') {
            $options['search_type'] = 'all';
        }
        /* Поиск среди продуктов */
        if(array_key_exists('products', $options) && is_array($options['products'])) {
            $products_ids = array();
            foreach ($options['products'] as $k => $v) {
                if(is_array($v) && array_key_exists('id', $v)) {
                    $products_ids[] = $v['id'];
                }  elseif(wa_is_int($v)) {
                    $products_ids[] = intval($v);
                }
            }
            if(empty($products_ids)) {
                unset($options['products']);
            } else {
                $options['products'] = $products_ids;
            }
        }
        /* Поиск среди типов продуктов */
        if(array_key_exists('types', $options)) {
            if(is_array($options['types'] )) {
                $types = array();
                foreach ($options['types'] as $v) {
                    if(wa_is_int($v)) {
                        $types[] = intval($v);
                    }
                }
                if(empty($types)) {
                    unset($options['types']);
                } else {
                    $options['types'] = $types;
                }
            } elseif(wa_is_int($options['types'])) {
                $options['types'] = intval($options['types']);
            } else {
                unset($options['types']);
            }
        }




        /* Review min_rate */
        if(array_key_exists('min_rate', $options)) {
            $options['min_rate'] = intval($options['min_rate']);
        } else {
            $options['min_rate'] = intval($this->getSettings('min_rate'));
        }
        $options['min_rate'] = ($options['min_rate'] < 1 || $options['min_rate'] > 5)? false : $options['min_rate'];
        /* Review max_rate */
        if(array_key_exists('max_rate', $options)) {
            $options['max_rate'] = intval($options['max_rate']);
        } else {
            $options['max_rate'] = intval($this->getSettings('max_rate'));
        }
        $options['max_rate'] = ($options['max_rate'] < 1 || $options['max_rate'] > 5)? false : $options['max_rate'];
        if(!empty($options['min_rate']) && $options['max_rate'] && $options['max_rate'] < $options['min_rate']) {
            $options['max_rate'] = false;
        }

        /* min_chars */
        if(array_key_exists('min_chars', $options)) {
            $options['min_chars'] = intval($options['min_chars']);
        } else {
            $options['min_chars'] = intval($this->getSettings('min_chars'));
        }
        $options['min_chars'] = ($options['min_chars'] < 10 || $options['min_chars'] > 3000)? false : $options['min_chars'];

        /* sort */
        if(!array_key_exists('sort', $options)) {
            $options['sort'] = $this->getSettings('sort');
        }
        $options['sort'] = strtolower($options['sort']);
        if(!array_key_exists($options['sort'], self::$sorts)) {
            $options['sort'] = 'desc';
        }

        /* Category */
        if(array_key_exists('category_id', $options)) {
            $options['category_id'] = intval($options['category_id']);
        } elseif(intval(self::$category_id)> 0) {
            $options['category_id'] = intval(self::$category_id);
        }
        /* Исключение показанных продуктов */
        if(array_key_exists('exclude_products', $options) &&  $options['exclude_products'] ==1) {
            $options['exclude_products'] = true;
        } else {
            unset($options['exclude_products']);
        }
        /* Для особо хитрых, можно и разрешить использование html в отзыве по спец параметру */
        $options['use_html'] = false;
        if(array_key_exists('use_html', $options) && $options['use_html']==1) {
            $options['use_html'] = true;
        }
        return $options;
    }
    protected function getScripts() {
        $html = '';
        if(!self::$init_scripts && self::isOn())  {
            $html .= '<link href="'.self::getPluginUrlStatic().'css/'.self::PLUGIN_ID.'Frontend.css" rel="stylesheet" type="text/css">';
            $html .= '<link href="'.self::getPluginUrlStatic().'css/owl/owl.carousel.min.css" rel="stylesheet" type="text/css">';
            $html .= '<link href="'.self::getPluginUrlStatic().'css/owl/owl.theme.default.css" rel="stylesheet" type="text/css">';
            $html .= '<script  src="'.self::getPluginUrlStatic().'js/owl/owl.carousel.min.js"></script>';
            $html .= self::getTemplate('css');
            self::$init_scripts = true;
        }
        return $html;
    }






}
