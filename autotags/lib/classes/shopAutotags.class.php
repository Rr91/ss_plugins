<?php

class shopAutotags {

    private static $sort = array(
        'homepage',
        'category',
        'product',
        'staticpage',
        'tagpage',
    );

    public static function sort($a, $b) {
        return array_search($a->getType(), self::$sort) > array_search($b->getType(), self::$sort) ? 1 : -1;
    }

    public static function getCurrentRouteHash() {
        $domain = wa()->getRouting()->getDomain(null, true);
        $route = wa()->getRouteUrl('shop/frontend');
        return md5($domain . $route . '*');
    }

    public static function getRouteHashs() {
        $route_hashs = array();
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $route_url = $domain . '/' . $route['url'];
                $route_hashs[$route_url] = md5($route_url);
            }
        }
        return $route_hashs;
    }

    public static function getEngines() {
        $engines = array();
        $path = wa()->getAppPath('plugins/autotags/lib/classes/engines/*.class.php', 'shop');
        $files = glob($path);
        foreach ($files as $file) {
            $class_name = basename($file, '.class.php');
            if (class_exists($class_name)) {
                $engine = new $class_name();
                if ($engine instanceof shopAutotagsEngine) {
                    $engines[$engine->getType()] = $engine;
                }
            }
        }
        uasort($engines, array('shopAutotags', 'sort'));
        return $engines;
    }

}
