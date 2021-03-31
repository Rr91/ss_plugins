<?php
class shopSeoreviewsPluginStorefronts {

    protected static $storefront = null;
    public static function getStorefronts()  {
        $routing = wa()->getRouting();
        $storefronts = array();
        $domains = $routing->getByApp('shop');
        // Пробегаем по доменам
        foreach ($domains as $domain => $domain_routes) {
            // Забираем все отдельные поселения
            foreach ($domain_routes as $route)  {
                $storefronts[] = $domain.'/'.$route['url'];
            }
        }
        return $storefronts;
    }
    public static function getStorefrontCode($storefront_name = '') {
        return md5($storefront_name);
    }
    public static function getStorefront($storefront = null) {
        if($storefront!==null) {
            self::$storefront = new shopSeoreviewsPluginStorefront($storefront);
        } elseif(self::$storefront==null) {
            $routing = wa()->getRouting();
            $domain = $routing->getDomain();
            $route = $routing->getRoute();
            $storefronts = self::getStorefronts();
            $currentRouteUrl = $domain.'/'.$route['url'];
            $storefront = in_array($currentRouteUrl, $storefronts) ? $currentRouteUrl : '';
            self::$storefront = new shopSeoreviewsPluginStorefront($storefront);
        }
        return  self::$storefront;
    }
}