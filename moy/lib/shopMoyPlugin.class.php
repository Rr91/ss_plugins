<?php

class shopMoyPlugin extends shopPlugin
{

	public function productSave(&$params)
	{
		$model = new shopProductFeaturesModel();
		$product = new shopProduct($params['data']['id']);
		$data = $model->getData($product);
		if($data['kolichestvo_v_upakovke'] && $product['price']){
			$data['tsena_za_shtuku'] = round($product['price']/$data['kolichestvo_v_upakovke'],2);
			$model->setData($product, $data);
		}
    	
	}
	public static function sOs()
	{
		$feature_price_ed = 125;
		$query = "SELECT spf.`product_id`, sp.price,sfvd.`value` FROM `shop_product_features` spf
					LEFT JOIN `shop_feature_values_double` sfvd
					ON spf.`feature_value_id` = sfvd.`id` AND spf.`feature_id` = sfvd.`feature_id`
					LEFT JOIN shop_product sp
					ON sp.id = spf.`product_id`
					WHERE spf.`feature_id` = 110 AND sfvd.`feature_id` = 110";
		$model = new shopProductFeaturesModel();
		$data = $model->query($query)->fetchAll();
		
		wa_dump($data);

	}

	public static function updateMoyArticle()
	{
		$plugin = wa('shop')->getPlugin('moy');
		$model = new shopProductSkusModel();
		$query = 'SELECT * FROM shop_product_skus WHERE id_1c != "" AND sku=""';
		$rs = $model->query($query)->fetchAll();
		foreach ($rs as $key => $value) {
			$externalCode = $value["id_1c"];
			// $externalCode = "n38HZ009igC6DIEFcnvKV3";
			$url_get="https://online.moysklad.ru/api/remap/1.2/entity/variant?filter=externalCode=$externalCode";
			$sql_query = "SELECT COUNT(*) as cnt FROM shop_product_skus WHERE product_id=".$value['product_id'];
			$cnt = $model->query($sql_query)->fetchField("cnt");
			if($cnt == 1){	
				$sql = "SELECT sku_id, id_1c FROM shop_product WHERE id=".$value['product_id'];
				$dat = $model->query($sql)->fetchAssoc();
				$sku_id = $dat["sku_id"];
				$id_1c = $dat["id_1c"];
				if($value["id"] == $sku_id){
					$url_get="https://online.moysklad.ru/api/remap/1.2/entity/product?filter=externalCode=$id_1c";
				}
			}
			$curl = $plugin->setupCurl();
			$curl1 = $plugin->setCurl(
				$curl,
				$url_get,
				"GET"
			);
			$data = $plugin->getData($curl1);
			curl_close($curl);
			if($data){
				$model->updateById($value["id"], array("sku"=>$data));
			}
		}
		
	}

	public function setupCurl()
	{
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);

	    $userName = "admin@lsadun1";
	    $userPassword = "189ba0c4c3";
	    curl_setopt($curl, CURLOPT_USERPWD, "$userName:$userPassword");
	    return $curl;
	}

	public static function curlExec($curlObject)
	{

	    $response = curl_exec($curlObject);

	    $curlErrorNumber = curl_errno($curlObject);
	    if ($curlErrorNumber) {
	        throw new Exception(curl_error($curlObject));
	    }
	    return $response;
	}
	public function setCurl(&$curlObject, $uri, $method=false)
	{
	    curl_setopt($curlObject, CURLOPT_URL, $uri);

	    curl_setopt($curlObject, CURLOPT_HTTPGET, true);

	    return $curlObject;
	}


	public function getData($curlObject)
	{
	    $response = self::curlExec($curlObject);
	    $data = json_decode($response, true);
	    $result = $data['rows'][0]["code"];
	    return $result;
	}

}
