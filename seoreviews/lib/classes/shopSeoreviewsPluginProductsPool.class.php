<?php
class shopSeoreviewsPluginProductsPool 
{

    /**
     * @var shopSeoreviewsPluginProductsPool
     */
    protected static $_instance = null;
    /**
     * Хранение данных продуктов из хука frontend_products
     * @var array
     */
    protected static $frontend_products = array();

    protected function __construct(){}
    protected function __clone() {}
    /**
     * @param shopSeoreviewsPlugin  protect uses)
     * @throws waException
     * @return shopSeoreviewsPluginProductsPool
     */
    public static function getInstance($obj = null)
    {
        if(!($obj instanceof shopSeoreviewsPlugin)) {
            throw new waException('No Access!');
        }
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;

    }

    /**
     * Добавляет продукты из хука frontend_products
     * @uses shopSeoreviewsPlugin::frontendProducts()
     * @param $products
     */
    public function addProducts($products)
    {
        foreach ($products as $product) {
            if(!array_key_exists('id', $product)) { // Паранойя
                continue;
            }
            $data = array(
                'id' => $product['id'],
                'image_id' => null,
                'name' => '',
                'frontend_url' => ''
            );

            if(array_key_exists('image_id', $product)) {
                $data['image_id'] = $product['image_id'];
            }
            if(array_key_exists('name', $product)) {
                $data['name'] = $product['name'];
            }
            if(array_key_exists('frontend_url', $product)) {
                $data['frontend_url'] = $product['frontend_url'];
            }
            self::$frontend_products[$product['id']] = $data;
        } 
    }
    public function getKeys(){
        return array_keys(self::$frontend_products);
    }
    
    public function getProducts()  {
        return self::$frontend_products;
    }
}
