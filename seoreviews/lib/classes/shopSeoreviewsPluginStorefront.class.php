<?php

/**
 * Объект витрины
 * Class shopSeoreviewsPluginStorefront
 */
class shopSeoreviewsPluginStorefront {
    /**
     * Название(URL) витрины
     * @var string
     */
    protected $name = '';

    /**
     * shopSeoreviewsPluginStorefront constructor.
     * @param null $storefront - Название(URL) витрины
     */
    public function __construct($storefront = null)  {
        $this->setStorefront($storefront);
    }

    /**
     * Проверка и установка данных витрины
     * @param null $storefront
     */
    protected function setStorefront($storefront = null) {
       $this->name = $storefront;
    }

    /**
     * Возвращает все темы дизайна, используемые на втирине
     * @return array
     */
    public function getThemes() {
            $storefront_data = self::splitUrl($this->getName());

            if($storefront_data) {
                $routing = wa('shop')->getRouting()->getRoutes($storefront_data['domain']);
                if(empty($routing)) {
                    $routing = wa('shop')->getRouting()->getRoutes();
                }
                foreach ($routing as $route) {
                    if($route['app'] == shopSeoreviewsPlugin::APP && $route['url'] == ltrim($storefront_data['url'], '/\\'))  {
                        $theme = new waTheme($route['theme'], shopSeoreviewsPlugin::APP);
                        $theme_mobile = ($route['theme'] == $route['theme_mobile'])? false : new waTheme($route['theme_mobile'], shopSeoreviewsPlugin::APP);
                        return array(
                            'theme' => $theme,
                            'theme_mobile' => $theme_mobile,
                        );
                    }
                }
            }
        return array();
    }

    /**
     * Разбирает адрес названия витрины на составляющие
     * @param $url
     * @return array|bool
     */
    public static function splitUrl($url)
    {
        if(preg_match('@^(?:http://|https://)?([^/]+)([\/].*)?@i', mb_strtolower($url), $url_arr)) {
            $domain = wa('shop')->getRouting()->getDomain($url_arr[1]);
            $u = trim(wa()->getRootUrl(), '/');
            if ($u) {
                $domain .= '/'.$u;
            }
            if(count($url_arr)==3) {
                return  array(
                    'domain' => $url_arr[1],
                    'url' => str_replace($domain,'',$url)
                );
            }
        }
        return false;
    }

    /**
     * Возвращает md5 код названия витрины
     * @return string
     */
    public function getCode() {
        return md5($this->name);
    }

    /**
     * Возвращает название витрины
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * При приведении класса к строке будет выведено название витрины
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}