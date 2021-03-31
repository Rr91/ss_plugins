<?php

class shopMobileappPlugin extends shopPlugin
{
	public static $countFilter = 0;

	public function orderActions($params) {
		if (is_numeric($params['order_id'])) {
			$model = new waModel();
			$order = $model->query("SELECT * FROM `shop_order` WHERE `id` = i:id", array(
				'id' => $params['order_id']
			))->fetch();
			if (is_array($order)) {
				$this->sendPush($order['contact_id'], $params['order_id'], $order['state_id']); // TODO
			}
		}
	}

	public function backendOrder($params) {
		$platform = '';
		if ($params['params']['user_agent'] == 'WebasystShopApp') {
			$platform = "(Заказ сделан из приложения для iOS)";
		}
		elseif ($params['params']['user_agent'] == 'WebasystShopAppAndroid') {
			$platform = "(Заказ сделан из приложения для Android)";
		}
		if ($platform != '') {
			return array(
'title_suffix' => <<<HTML
<style>
#s-order-title:after {
	color: #f57209;
	content: '$platform';
	display: block;
	font-size: 16px;
}
</style>
HTML
	    );
		}
		return array();
	}

	public function productsCollectionFilter($collection)
	{
		$collection_hash = $collection->getHash();
		if (is_array($collection_hash) && reset($collection_hash) == 'category' && wa()->getEnv() == 'frontend') {
			if (self::$countFilter == 0) {
				$price_min = waRequest::get("price_min_multi", null);
				$price_max = waRequest::get("price_max_multi", null);
				if (is_numeric($price_min) or is_numeric($price_max)) {
					$route_hash = shopPriceRouteHelper::getRouteHash();
	        $price_model = new shopPricePluginModel();
	        $category_ids = shopPricePlugin::getUserCategoryId();
	        $prices = $price_model->getPrices($route_hash, $category_ids);
					if (is_array($prices) and count($prices)) {
						$keys = array_keys($prices);
						$price_id = $keys[0];
						if (is_numeric($price_id)) {
							if (is_numeric($price_min)) {
								$alias = $collection->addJoin(array(
									 'type'  => 'LEFT',
									 'table' => 'shop_product_skus',
									 'on' => "p.sku_id = :table.id",
									 'where' => ":table.price_plugin_{$price_id} >= '{$price_min}'",
								));
							}
							if (is_numeric($price_max)) {
								if (isset($alias)) {
									$collection->addWhere("{$alias}.price_plugin_{$price_id} <= '{$price_max}'");
								}
								else {
									$alias = $collection->addJoin(array(
										 'type'  => 'LEFT',
										 'table' => 'shop_product_skus',
										 'on' => "p.sku_id = :table.id",
										 'where' => ":table.price_plugin_{$price_id} <= '{$price_max}'",
									));
								}
							}
						}
					}
					self::$countFilter++;
				}
			}
		}
	}

	public function sendPushTest($name, $values) {
$html = <<<HTML
<input type="text" id="notification_title" placeholder="Заголовок" style="margin-bottom:10px;padding:10px;width:95%;"><br/>
<textarea id="notification_text" placeholder="Введите текст уведомления" style="padding:10px;width:95%;"></textarea><br/>
<div style="margin-top: 10px;display: flex;align-items:center;">
	Тип устройства:&nbsp;
	<select name="type" id="notification_type">
		<option value="all">Все</option>
		<option value="apple">Apple</option>
		<option value="android">Android</option>
	</select>
	<button id="notification_send" class="button blue" style="margin-left: auto;">Отправить</button>
</div>
<script>
$(function(){
	$("#plugins-settings-form .submit").hide();
	$("#notification_send").click(function(){
		if ($("#notification_text").val().trim().length > 0 && $("#notification_title").val().trim().length > 0) {
			$.post("?plugin=mobileapp&action=notification", {message: $("#notification_text").val(), title: $("#notification_title").val(), type: $("#notification_type").val()}, function(res){
					if (res.status == 'ok') {
						alert("Уведомление успешно отправлено!");
					}
					else {
						alert("Произошла ошибка при отправке уведомления.");
					}
			});
		}
		else {
			alert("Заполните обязательные поля.");
		}
		return false;
	});
});
</script>
HTML;
		return $html;
	}

	public static function setCookie($key, $value) {
		wa()->getResponse()->setCookie($key, $value, time() + 30 * 86400, null, '', false, true);
	}

	public static function getDiscountProducts($category_id = 0, $limit = 10) {
		if (is_numeric($category_id)) {
			$route_hash = shopPriceRouteHelper::getRouteHash();
			$price_model = new shopPricePluginModel();
			$category_ids = shopPricePlugin::getUserCategoryId();
			$prices = $price_model->getPrices($route_hash, $category_ids);
			if (is_array($prices) and count($prices)) {
				$keys = array_keys($prices);
				$price_id = $keys[0];
				$page = waRequest::get('page', 0 , 'int');
				$offset = (is_numeric($page) and $page > 0)? ($page * $limit) - $limit : 0;
				$collection = new shopProductsCollection("*");
				$collection->addJoin(array(
					 'type'  => 'LEFT',
					 'table' => 'shop_product_skus',
					 'on' => "p.sku_id = :table.id",
					 'where' => ":table.price_plugin_compare_{$price_id} > 0",
				));
				if ($category_id > 0) {
					$collection->addWhere("p.category_id = '{$category_id}'");
				}
				return $collection->getProducts($fields = "*", $offset, $limit);
			}
		}
		return array();
	}

	public static function getCountDiscountProducts($category_id = 0, $limit = 10) {
		if (is_numeric($category_id)) {
			$route_hash = shopPriceRouteHelper::getRouteHash();
			$price_model = new shopPricePluginModel();
			$category_ids = shopPricePlugin::getUserCategoryId();
			$prices = $price_model->getPrices($route_hash, $category_ids);
			if (is_array($prices) and count($prices)) {
				$keys = array_keys($prices);
				$price_id = $keys[0];
				$collection = new shopProductsCollection("*");
				$collection->addJoin(array(
					 'type'  => 'LEFT',
					 'table' => 'shop_product_skus',
					 'on' => "p.sku_id = :table.id",
					 'where' => ":table.price_plugin_compare_{$price_id} > 0",
				));
				if ($category_id > 0) {
					$collection->addWhere("p.category_id = '{$category_id}'");
				}
				$count = $collection->count();
				return $count > 0 ? ceil($count/$limit) : 0;
			}
		}
		return 0;
	}

	public static function getCurrentCityArray()
	{
		$routing = new shopRegionsRouting();

		$current_city = $routing->getCurrentCity();

		$current_city_array = array(
			'id' => '',
			'region_code' => '',
			'country_iso3' => '',
			'name' => '',
		);

		if ($current_city)
		{
			$current_city->getCountryName();
			$current_city_array = $current_city->toArray(false, false);
		}

		return $current_city_array;
	}

	public static function getAuthToken() { // TODO Auto domains
		$token = waRequest::cookie('auth_token');
		wa()->getResponse()->setCookie('auth_token', $token, time() + 30 * 86400, '/', 'colornumbers.ru', false, true);
		wa()->getResponse()->setCookie('auth_token', $token, time() + 30 * 86400, '/', 'www.colornumbers.ru', false, true);
		wa()->getResponse()->setCookie('auth_token', $token, time() + 30 * 86400, '/', 'vdk.colornumbers.ru', false, true);
	}

	public static function logoutMyApp() { // TODO Auto domains
		$contact_id = wa()->getUser()->getId();
    if ($contact_id) {
      wa()->getUser()->logout();
			wa()->getResponse()->setCookie('auth_token', null, -1, '/', 'colornumbers.ru', false, true);
			wa()->getResponse()->setCookie('auth_token', null, -1, '/', 'www.colornumbers.ru', false, true);
			wa()->getResponse()->setCookie('auth_token', null, -1, '/', 'vdk.colornumbers.ru', false, true);
			wa()->getResponse()->setCookie('auth_token', null, -1, '/', '.colornumbers.ru', false, true);
		}
		wa()->getResponse()->redirect('/login/?global_reload=true');
	}

	public function getCookies() {
		return waRequest::cookie();
	}

	public static function isTokenUser($type = 'apple') {
		$model = new shopMobileappPluginModel();
		$token = waRequest::cookie('device_token', null);
		$contact_id = wa()->getUser()->getId();
		if (is_null($token)) {
			$token = wa()->getStorage()->read('device_token');
			if ($token) {
				wa()->getResponse()->setCookie('device_token', $token, time() + 30 * 86400, '/', '.colornumbers.ru', false, true);
			}
		}
		if ($token) {
			$check = $model->getToken($token, $type);
			if (isset($check['id'])) {
				if ($check['user_id'] > 0 && !$contact_id) {
					$model->editDeviceUserID($token, $type, 0);
				}
				elseif ($check['user_id'] != $contact_id && $contact_id) {
					$model->editDeviceUserID($token, $type, $contact_id);
				}
			}
		}
	}

	public static function isEmptyToken() {
		$token = waRequest::cookie('device_token', null);
		if (is_null($token)) {
			$token = wa()->getStorage()->read('device_token');
			if ($token) {
				wa()->getResponse()->setCookie('device_token', $token, time() + 30 * 86400, '/', '.colornumbers.ru', false, true);
			}
		}
		return $token;
	}

	private function getStatusName($key) {
		$workflow = new shopWorkflow();
		$states = $workflow->getAvailableStates();
		if (isset($states[$key]['name'])) {
			return $states[$key]['name'];
		}
		return $key;
	}

	public static function sendPushApple($tokens, $title, $msg) {
		ob_start();
		$autoloadVendorPath = wa()->getAppPath('plugins/mobileapp/lib/vendor/ApnsPHP/Autoload.php', 'shop');
		$pathCertificate = wa()->getAppPath('plugins/mobileapp/lib/certificate/', 'shop');
		require_once $autoloadVendorPath;
		$push = new ApnsPHP_Push(
			ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION,
			$pathCertificate.'server_certificates_bundle_sandbox.pem'
		);
		$push->setRootCertificationAuthority($pathCertificate.'entrust_root_certification_authority.pem');
		$push->connect();
		foreach($tokens as $token) {
			$message = new ApnsPHP_Message($token);
			$message->setCustomIdentifier("Notification"); // TODO
			$message->setBadge(1);  // TODO
			$message->setTitle($title);
			$message->setText($msg);
			$message->setSound();
			$message->setExpiry(30);
			@$push->add($message);
		}
		@$push->send();
		@$push->disconnect();
		ob_get_clean();
	}

	public static function sendPushAndroid($tokens, $title, $message) {
		define( 'API_ACCESS_KEY', 'AAAA0O4tIqk:APA91bFccfp-DLnluBXRQtqruhsYUEHxvdgEk8Bo6X_zSvV97U-P98_j5xq_BJszp7WFyWcIn2Pd1eNbswN3XP7JYZ3IlLEZGNDIPh7R9YZDy7XM3qsInGMXm076bQ6R9-mLcqZt-x3O' ); // TODO settings

		$msg = array(
				'title' => $title,
				'message' => $message,
				//'subtitle'      => "Под заголовок",
				//'tickerText'    => "какой то текст",
				'vibrate' => true,
				'sound' => true
		);

		$msg = array_diff($msg, array(''));

		$tokensStep = array_chunk($tokens, 1000);

		foreach($tokensStep as $tokens1000) {
			$fields = array(
					'registration_ids' => $tokens1000,
					'data' => $msg
			);
			$headers = array(
					'Authorization: key=' . API_ACCESS_KEY,
					'Content-Type: application/json'
			);
			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch);
			curl_close( $ch );
			waLog::dump(json_decode($result, true), "send_push_android_result.log");
		}
		//return json_decode($result, true);
	}

	private function sendPush($userID, $orderID, $statusNew) {
		$model = new shopMobileappPluginModel();
		$devices = $model->getTokenByUserID($userID);

		if (is_array($devices) and count($devices)) {
			$appleDevices = array();
			$androidDevices = array();
			foreach($devices as $device) {
				if ($device['type'] == 'apple') {
					$appleDevices[] = $device['token'];
				}
				else {
					$androidDevices[] = $device['token'];
				}
			}
			$title = "Изменился статус заказа №{$orderID}";
			$msg = "Новый статус заказа: {$this->getStatusName($statusNew)}";
			if (count($appleDevices)) {
				self::sendPushApple($appleDevices, $title, $msg);
			}
			if (count($androidDevices)) {
				self::sendPushAndroid($androidDevices, $title, $msg);
			}
		}
	}
}
