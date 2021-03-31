<?php

/*
 *
 * Processingsuppliers plugin for Webasyst framework.
 *
 * @name Processingsuppliers
 * @author EasyIT LLC
 * @link http://easy-it.ru/
 * @copyright Copyright (c) 2017, EasyIT LLC
 * @version    1.2, 2017-04-06
 *
 */

class shopProcessingsuppliersPluginBackendFileUploadController extends waLongActionController
{
    const MARKUP_ABSOLUTE = 1;
    const MARKUP_PERCENTAGE = 2;

    protected $model;
    protected $fileDataModel;
    protected $fileModel;
    protected $skuModel;
    protected $productModel;
    protected $productImagesModel;
    protected $categoryModel;
    protected $categoryProductModel;
    protected $featureModel;
    protected $productStockModel;
    protected $currencyModel;

    protected $currentCategory;
    protected $fileId;
    protected $logFile;
    protected $supplier;
    protected $settings;
    protected $rows;
    protected $errors;
    protected $supplierId;

    public function __construct()
    {
        $this->model = new waModel();
        $this->fileDataModel = new shopProcessingsuppliersFileDataModel();
        $this->skuModel = new shopProductSkusModel();
        $this->productModel = new shopProductModel();
        $this->productImagesModel = new shopProductImagesModel();
        $this->categoryModel = new shopCategoryModel();
        $this->categoryProductModel = new shopCategoryProductsModel();
        $this->featureModel = new shopFeatureModel();
        $this->productStockModel = new shopProductStocksModel();
        $this->currencyModel = new shopCurrencyModel();

        $this->fileModel = new shopProcessingsuppliersFileModel();
        $supplierModel = new shopProcessingsuppliersSupplierModel();

        // v1.1 added supplier settings
        $this->fileId = waRequest::get('id', 0, waRequest::TYPE_INT);
        $this->supplierId = $this->fileModel->getSupplierIdById($this->fileId);
        $this->supplier = $supplierModel->getById($this->supplierId);
        $this->settings = shopProcessingsuppliersPlugin::getCorrectSettings($this->supplier);
    }

    public function execute()
    {
        try {
            parent::execute();
        } catch (waException $ex) {
            if ($ex->getCode() == '302') {
                echo json_encode(array('warning' => $ex->getMessage()));
            } else {
                echo json_encode(array('error' => $ex->getMessage()));
            }
        }
    }

    public function init()
    {
        $this->data['report_add'] = array();
        $this->data['timestamp'] = time();
        $this->data['total_count'] = $this->fileDataModel->countByField('file_id', $this->fileId);
        $this->data['offset'] = 0;
    }

    public function info()
    {
        $interval = 0;
        if (!empty($this->data['timestamp'])) {
            $interval = time() - $this->data['timestamp'];
        }
        $response = array(
            'time' => sprintf('%d:%02d:%02d', floor($interval / 3600), floor($interval / 60) % 60, $interval % 60),
            'processId' => $this->processId,
            'progress' => 0.0,
            'ready' => $this->isDone(),
            'offset' => $this->data['offset']
        );
        $response['progress'] = ($this->data['offset'] / $this->data['total_count']) * 100;
        // var_dump($this->data['offset'] , $this->data['total_count']);
        // var_dump($response['progress']);
        $response['progress'] = sprintf('%0.3f%%', $response['progress']);
        // var_dump($response['progress']);
        // exit;

        echo json_encode($response);
    }

    private function getParsedColumns($rowData)
    {
        $supplierColumnPropertyModel = new shopProcessingsuppliersSupplierColumnPropertyModel();
        $columns = json_decode($this->supplier['columns'], true);
        foreach ($columns as $k => $column) {
            $property = $supplierColumnPropertyModel->getProperty($column['property']);
            $columns[$column['property']] = array_merge($column, $property);
            $columns[$column['property']]['value'] = $rowData[$column['property']];
            unset($columns[$k]);
        }

        return $columns;
    }

    private function isSitePriceGreaterThanFilePrice($sitePrice, $siteCurrency, $filePrice, $fileCurrency)
    {
        $filePrice = $this->currencyModel->convert($filePrice, $fileCurrency, $siteCurrency);
        return ($sitePrice > $filePrice);
    }

    private function convertCurrency($price, $from, $to)
    {
        return shopRounding::roundCurrency($this->currencyModel->convert($price, $from, $to), $to);
    }

    protected function getLockWaitTime()
    {
       return 4;
    }

    public function step()
    {
    	// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

    	set_time_limit(0);
        $this->rows = $this->fileDataModel->getByFileId($this->fileId, $this->data['offset'], $this->settings['limit']);

        if (!$this->rows) {
            throw new waException('В данном файле нет строк');
        }
        $i = 0;
        foreach ($this->rows as $row) {
            $rowData = json_decode($row['data'], true);
            $rowData = $this->getParsedColumns($rowData);
            foreach ($rowData as $property) {
                if ($property['type'] === 'string') {
                    if(is_array($property['value'])) {
                        $str = '';
                        foreach ($property['value'] as $key => $value) {
                            if($value != end($property['value'])) {
                                $str = $str . $value . ",";
                            } else {
                                $str = $str . $value;
                            }

                        }
                        $rowData[$property['property']]['value'] = htmlspecialchars_decode($str, ENT_QUOTES);
                    } else {
                        $rowData[$property['property']]['value'] = htmlspecialchars_decode($property['value'], ENT_QUOTES);
                    }
                }
            }

            if(!isset($rowData['sku@price']['value'])) {
                $rowData['sku@price']['value'] = 0;
                $rowData['sku@price']['type'] = "price";
            }

            if(!isset($rowData['sku@sku']['value'])) {
                $rowData['sku@sku']['value'] = '';
                $rowData['sku@sku']['type'] = "string";
            }
            // TODO: логирование
            $sku = $this->skuModel->getByField('sku', $rowData['sku@sku']['value']);
            if(empty($this->data['currentCategory'])) {
                $rowData['product@category_id']['value'] = 0;
                $rowData['product@category_id']['type'] = "string";
            } else {
                $rowData['product@category_id'] = $this->data['currentCategory'];
            }
            if (!$sku) {
                if (strpos($rowData['product@name']['value'], '"')) {
                    $rowData['product@name'] = str_replace('"', "`", $rowData['product@name']);
                }
                if (strpos($rowData['product@name']['value'], "'")) {
                    $rowData['product@name'] = str_replace("'", "`", $rowData['product@name']);
                }
                if($this->settings['add_new']){   
                    $this->data['report_add'][$rowData['sku@sku']['value']] = $this->fileDataModel->setNewProduct($rowData,$this->supplierId, $this->settings['category_new']);
                }
                continue;
            }

            $product = $this->productModel->getById($sku['product_id']);

            if (!$product && $this->settings['add_new']) {
                $rowData['product@name'] = str_replace('"', "'", $rowData['product@name']);
                $this->data['report_add'][$rowData['sku@sku']['value']] = $this->fileDataModel->setNewProduct($rowData,$this->supplierId, $this->settings['category_new']);
                continue;
            }
          
            // если в прайсе есть информация по доступности артикула, то используем ее, иначе пытаемся ее определить с помощью настроек на основании остатков
            // TODO: сейчас в прайсе воспринимаются только цифры, нужно сделать, чтобы в прайсе можно было указывать слова, например Доступен
            if (isset($rowData['sku@available'])) {
            	$rowData['sku@available']['property'] = 'sku@available';
            	$rowData['sku@available']['type'] = 'string';
            	$rowData['sku@available']['group'] = 'Параметры артикула';
                $rowData['sku@available']['value'] = ($this->getAvialabilityByWord($rowData['sku@available']['value']) == false) ? 0 : 1;
            } else {
            	$rowData['sku@available']['property'] = 'sku@available';
            	$rowData['sku@available']['type'] = 'string';
            	$rowData['sku@available']['group'] = 'Параметры артикула';
                $rowData['sku@available']['value'] = $this->getSkuAvailability($sku['id'], $this->countTotalStocks($rowData), $sku['count'], $sku['available'], $rowData);
            }
            $rowData['sku@available']['value'] = true;

            // если в прайсе есть информация по видимости товара, то используем ее, иначе пытаемся ее определить с помощью доступности артикула
            // TODO: сейчас в прайсе воспринимаются только цифры, нужно сделать, чтобы в прайсе можно было указывать слова, например Скрыт
            if (isset($rowData['product@status'])) {
                $rowData['product@status']['property'] = 'product@status';
                $rowData['product@status']['type'] = 'string';
                $rowData['product@status']['group'] = 'Параметры товара';
                $rowData['product@status']['value'] = ((int)$rowData['product@status']['value'] == false) ? 0 : 1;
            } else {
                $rowData['product@status']['property'] = 'product@status';
                $rowData['product@status']['type'] = 'string';
                $rowData['product@status']['group'] = 'Параметры товара';
                $rowData['product@status']['value'] = $this->getProductStatus($product['id'], $sku['id'], $rowData['sku@available']['value'], $product['status']);
            }

            // генерируем url для товара, если он есть в прайсе
            if (isset($rowData['product@url'])) {
                $rowData['product@url']['value'] = $this->genProductUrl($rowData['product@url']['value'], $product['id']);
            }

            // наценка двух типов
            if ($this->settings['add_markup']) {
                if ($this->settings['markup_type'] == self::MARKUP_ABSOLUTE) {
                    $rowData['sku@price']['value'] += $this->settings['markup'];
                }

                if ($this->settings['markup_type'] == self::MARKUP_PERCENTAGE) {
                    $rowData['sku@price']['value'] += $rowData['sku@price']['value'] / 100 * $this->settings['markup'];
                }
            }

            // если в прайсе есть валюта, и при этом Shop-Script ее понимает, то берем ее из прайса (т.е. ничего не делаем), а если нет - из настроек
            // TODO: сделать конвертер непонимаемых валют в понимаемые, например преобразовывать руб => RUB, $ => USD и т.д.
            if (!isset($rowData['product@currency']) || empty($rowData['product@currency']['value']) || !$this->currencyModel->getById($rowData['product@currency']['value'])) {
            	$rowData['product@currency']['property'] = 'product@currency';
            	$rowData['product@currency']['type'] = 'string';
            	$rowData['product@currency']['group'] = 'Параметры товара';
                $rowData['product@currency']['value'] = ($this->isExistPriceColumns($rowData) && $this->settings['currency']) ? $this->settings['currency'] : $product['currency'];
            }

            // если в прайсе есть информация по цене и она больше текущей цены, то обновляем цену, а если меньше текущей - смотрим настройки
            if (isset($rowData['sku@price'])) {
                if($this->isSitePriceGreaterThanFilePrice($sku['price'], $product['currency'], $rowData['sku@price']['value'], $rowData['product@currency']['value'])) {
                    // если цена на сайте больше, чем в прайсе, и в настройках запрещено обновлять бОльшую цену, то просто переводим текущую цену в новую валюту
                    if (!$this->settings['update_greater_price']) {
                        $rowData['sku@price']['value'] = $this->convertCurrency($sku['price'], $product['currency'], $rowData['product@currency']['value']);
                    }
                }
            } else {
                // если информации по цене нет, то переводим ее в новую валюту
                // это нужно для сохранения правильной цены, если мы не обновляем цену, но меняем валюту
                $rowData['sku@price']['value'] = $this->convertCurrency($sku['price'], $product['currency'], $rowData['product@currency']['value']);
            }

            // если информации по закупочной цене нет, то переводим ее в новую валюту
            // это нужно для сохранения правильной цены, если мы не обновляем закупочную цену, но меняем валюту
            if (!isset($rowData['sku@purchase_price'])) {
            	$rowData['sku@purchase_price']['property'] = 'sku@purchase_price';
            	$rowData['sku@purchase_price']['type'] = 'price';
            	$rowData['sku@purchase_price']['group'] = 'Параметры товара';
                $rowData['sku@purchase_price']['value'] = $this->convertCurrency($sku['purchase_price'], $product['currency'], $rowData['product@currency']['value']);
            }

            // если информации по зачеркнутой цене нет, то переводим ее в новую валюту
            // это нужно для сохранения правильной цены, если мы не обновляем зачеркнутую цену, но меняем валюту
            if (!isset($rowData['sku@compare_price'])) {
            	$rowData['sku@compare_price']['property'] = 'sku@compare_price';
            	$rowData['sku@compare_price']['type'] = 'price';
            	$rowData['sku@compare_price']['group'] = 'Параметры товара';
                $rowData['sku@compare_price']['value'] = $this->convertCurrency($sku['compare_price'], $product['currency'], $rowData['product@currency']['value']);
            }

            // если в прайсе есть информация по остаткам, то формируем массив остатков в требуемом формате, прошлую информацию по остаткам стираем
            if ($stockColumns = $this->getOnlyStockColumns($rowData)) {
                $rowData['sku@stock']['value'] = $this->getStocksArrayByColumns($stockColumns);
                foreach ($stockColumns as $k => $property) {
                    unset($rowData[$k]);
                }
            }

            // главный массив данных для обновления
            $data = array(
                'id' => $product['id'],
                'skus' => array($sku['id'] => array()),
                'features' => array()
            );

            if(isset($rowData['sku@price']["value"]) && $rowData['sku@price']["value"]){ 
                $rowData['sku@price']["value"] = ceil($rowData['sku@price']["value"]);
            }
            if(isset($rowData['sku@purchase_price']["value"]) && $rowData['sku@purchase_price']["value"]){ 
                $rowData['sku@purchase_price']["value"] = ceil($rowData['sku@purchase_price']["value"]);
            }
            if(isset($rowData['sku@compare_price']["value"]) && $rowData['sku@compare_price']["value"]){ 
                $rowData['sku@compare_price']["value"] = ceil($rowData['sku@compare_price']["value"]);
            }
            // заполняем главный массив данными из существующих свойств
            if($rowData["sku@price"]["value"] == 0){ unset($rowData["sku@price"]);}
            // wa_dump($rowData);
            foreach ($rowData as $p => $property) {
                $propertyInfo = explode('@', $p);
                $entity = $propertyInfo[0];
                $prop = $propertyInfo[1];
                switch ($entity) {
                    case 'product':
                        $data[$prop] = $property['value'];
                        break;
                    case 'sku':
                        $data['skus'][$sku['id']][$prop] = $property['value'];
                        break;
                    case 'feature':
                        $data['feature'][$prop] = $property['value'];
                        break;
                    default:
                        throw new waException(sprintf('Неизвестный тип свойства: %s', $entity));
                        break;
                }
            }
            $data["category_id"] = $product["category_id"];
            $this->update($data);
        }

        $this->data['offset'] += $this->settings['limit'];
    }

    public function getAvialabilityByWord($word)
    {
        if((int)$word) return true;
        
        $prepare_word = mb_strtolower(trim($word));
        $dictonary = array(
            "да","есть","в наличии",
        );
        if(in_array($prepare_word, $dictonary)) return true;
        return false;
    }

    public function isDone()
    {
        return $this->data['offset'] >= $this->data['total_count'];
    }

    public function finish($filename)
    {
        $this->info();

        if($this->isDone()){
            $this->sendReport();
            return true;
        } else {
            return false;
        }
    }

    public function sendReport(){
        if($this->settings['report_email']){
            $https = waRequest::server("HTTP_X_FORWARDED_PROTO"); if(!$https) $https = "http";
            $site  = $https."://".waRequest::server("HTTP_HOST");
            $subject = "Отчет о новых товарах".$site;
            $body = "Обновлений нет";
            if($this->data['report_add']){
                $body = "Список добавленных товаров :<ol>";
                foreach ($this->data['report_add'] as $pname => $pid) {
                    if($pid) $body .= '<li><a target="blank" href="'.$site.'/webasyst/shop/?action=products#/product/'.$pid.'/">'.$pname.'</li>';
                }
                $body .= "</ol>";
            }
            $mail_message = new waMailMessage($subject, $body);
            $mail_message->setTo($this->settings['report_email']);
            $mail_message->send();
        }
    }


    private function isExistPriceColumns($rowData)
    {
        foreach ($rowData as $property) {
            if ($property['type'] === 'price') {
                return true;
            }
        }

        return false;
    }

    private function isExistStockColumns($rowData)
    {
        foreach ($rowData as $property) {
            if ($property['type'] === 'stock') {
                return true;
            }
        }

        return false;
    }

    private function getOnlyStockColumns($rowData)
    {
        foreach ($rowData as $k => $property) {
            if ($property['type'] !== 'stock') {
                unset($rowData[$k]);
            }
        }

        if (empty($rowData)) {
            return array();
        }

        return $rowData;
    }

    private function countTotalStocks($rowData)
    {
        $total = 0;
        foreach ($this->getOnlyStockColumns($rowData) as $property) {
            $total += $property['value'];
        }

        return $total;
    }

    private function getStocksArrayByColumns($rowData)
    {
        $rowData = $this->getOnlyStockColumns($rowData);

        $stocks = array();
        foreach ($rowData as $property) {
            $stock_id = preg_replace('/[^0-9]/', '', $property['property']);
            $stocks[$stock_id] = $property['value'];
        }

        return $stocks;
    }

    private function genProductUrl($url, $product_id)
    {
        $isExistUrl = $this->productModel->countByField(array('id' => $product_id, 'url' => $url));
        if ($isExistUrl) {
            return $url;
        }

        return shopHelper::genUniqueUrl($url, $this->productModel);
    }

    private function getCategories($product_id)
    {
        $categories = $this->categoryProductModel->select('category_id')
            ->where('product_id = '.(int)$product_id)
            ->fetchAll('category_id');

        return array_keys($categories);
    }

    private function getTags($product_id)
    {
        $sql = "SELECT
                      `name`
                    FROM
                      `shop_tag` AS `st`
                      LEFT JOIN `shop_product_tags` AS `spt`
                        ON `spt`.`tag_id` = `st`.`id`
                    WHERE `product_id` = i:product_id
                    ORDER BY `id` ";
        $tags = $this->model->query($sql, array('product_id' => $product_id))->fetchAll('name');
        $tags = array_keys($tags);

        return implode(',', $tags);
    }

    private function getProductStatus($product_id, $sku_id, $currentSkuAvailability, $oldStatus)
    {
        if ($this->settings['update_product_visibility']) {
            $count = $this->skuModel->select('COUNT(id) AS count')
                                    ->where('product_id = i:product_id AND id != i:sku_id AND available = 1', array('product_id' => $product_id, 'sku_id' => $sku_id))
                                    ->fetchField('count');
            $count += $currentSkuAvailability;

            if (!$count) {
                return 0;
            }

            return 1;
        }

        return $oldStatus;
    }

    private function getSkuAvailability($sku_id, $newCount, $oldCount, $oldStatus, $rowData)
    {
        if ($this->settings['update_sku_availability']) {
            if ($newCount) {
                return 1;
            }

            $stockCounts = $this->getStocksArrayByColumns($rowData);
            $stocks = $this->getStocksArray($sku_id);
            foreach ($stockCounts as $stock_id => $count) {
                if ($stock_id) {
                    $stocks[$stock_id] = $count;
                }
            }

            $totalCount = 0;
            foreach ($stocks as $stock) {
                // если значение не указано (в админке это бесконечность), т.е. скрывать точно не нужно
                if ($stock === '') {
                    $totalCount++;
                    break;
                }

                $totalCount += (int)$stock;
            }

            if ($totalCount) {
                return 1;
            }

            if (!$newCount && $this->isExistStockColumns($rowData)) {
                return 0;
            }

            if ($oldCount) {
                return 1;
            }

            return 0;
        }

        return $oldStatus;
    }

    private function getStocks($sku_id, $newCount, $oldCount)
    {
        if ($this->settings['update_stock']) {
            $stock_id = $this->settings['stock'];

            if ($stock_id) {
                $stocks = $this->getStocksArray($sku_id);
                $stocks[$stock_id] = $newCount;

                return $stocks;
            }

            return array($newCount);
        } else {
            $counts = $this->productStockModel->getCounts($sku_id);
            if (!$counts) {
                return array($oldCount);
            }

            return $this->getStocksArray($sku_id);
        }
    }

    private function getStocksArray($sku_id)
    {
        $sql = "SELECT DISTINCT
                  `id`,
                  '' AS `count`
                FROM
                  `shop_stock` AS `ss`,
                  `shop_product_stocks`
                WHERE NOT EXISTS
                  (SELECT
                    *
                  FROM
                    `shop_product_stocks` AS `sps`
                  WHERE `ss`.`id` = `sps`.`stock_id`
                    AND `sps`.`sku_id` = i:sku_id)
                  AND `sku_id` = i:sku_id
                UNION
                ALL
                SELECT DISTINCT
                  `id`,
                  `count`
                FROM
                  `shop_stock` AS `ss`,
                  `shop_product_stocks`
                WHERE EXISTS
                  (SELECT
                    *
                  FROM
                    `shop_product_stocks` AS `sps`
                  WHERE `ss`.`id` = `sps`.`stock_id`
                    AND `sps`.`sku_id` = i:sku_id)
                  AND `sku_id` = i:sku_id
                  AND `id` = `stock_id`
                ORDER BY `id`";
        $stocks = $this->model->query($sql, array('sku_id' => $sku_id))->fetchAll('id');

        if (!$stocks) {
            $sql = "SELECT
                      `id`,
                      '' AS `count`
                    FROM
                      `shop_stock` ";
            $stocks = $this->model->query($sql)->fetchAll('id');
        }

        foreach ($stocks as &$stock) {
            $stock = $stock['count'];
        }

        return $stocks;
    }

    private function update($data)
    {
        if(isset($data["name"])) unset($data["name"]);
        $id = (empty($data['id']) || !intval($data['id'])) ? null : $data['id'];
        if (!$id && isset($data['id'])) {
            unset($data['id']);
        }

        # edit product info - check rights
        if ($id) {
            if (!$this->productModel->checkRights($id)) {
                throw new waRightsException(_w("Access denied"));
            }
        } else {
            if (!$this->productModel->checkRights($data)) {
                throw new waRightsException(_w("Access denied"));
            }
        }

        $features_model = new shopFeatureModel();
        $features_value_model = new shopFeatureValuesVarcharModel();

        # verify sku_type before save
        if (!empty($data['type_id'])) {
            if ($this->featureModel->isTypeMultipleSelectable($data['type_id'])) {
                if ($data['sku_type'] == shopProductModel::SKU_TYPE_SELECTABLE) {
                    if (empty($data['features_selectable'])) {
                        throw new waException(_w("Check at least one feature value"));
                    }
                }
            } else {
                $data['sku_type'] = shopProductModel::SKU_TYPE_FLAT;
            }
        } else {
            $data['sku_type'] = shopProductModel::SKU_TYPE_FLAT;
        }

        if ($data['sku_type'] == shopProductModel::SKU_TYPE_FLAT) {
            $data['features_selectable'] = array();
        }

        try {
            $product = new shopProduct($id);

            // for logging changes in stocks
            shopProductStocksLogModel::setContext(shopProductStocksLogModel::TYPE_PRODUCT);
            if ($product->save($data, true, $this->errors)) {
                $features_counts = null;
                if ($product->sku_type == shopProductModel::SKU_TYPE_SELECTABLE) {
                    $features_counts = array();
                    foreach ($product->features_selectable as $f) {
                        if (isset($f['selected'])) {
                            $features_counts[] = $f['selected'];
                        } else {
                            $features_counts[] = count($f['values']);
                        }
                    }
                }

                if($data['category_id']) {
                	$category_data = $this->categoryProductModel->getByField("product_id",$product['id']);
                	if(!isset($category_data['category_id']) && $category_data['category_id'] != $data['category_id']) {
                		$this->categoryProductModel->add($id,$data['category_id']);
                	}
		        }

                shopProductStocksLogModel::clearContext();

                if ($id) {
                    $this->logAction('product_edit', $id);
                }
            }
        } catch (Exception $ex) {
            throw new waException($ex->getMessage());
        }
    }

    protected function curl_exec_follow($ch, &$maxredirect = null) {

          // we emulate a browser here since some websites detect
          // us as a bot and don't let us do our job
          $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
                        " Gecko/20041107 Firefox/1.0";
          curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );

          $mr = $maxredirect === null ? 5 : intval($maxredirect);

          if (0) {

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

          } else {

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

            if ($mr > 0)
            {
                $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                $newurl = $original_url;

                $rch = curl_copy_handle($ch);

                curl_setopt($rch, CURLOPT_HEADER, true);
                curl_setopt($rch, CURLOPT_NOBODY, true);
                curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
                do
                {
                    curl_setopt($rch, CURLOPT_URL, $newurl);
                    $header = curl_exec($rch);
                    if (curl_errno($rch)) {
                        $code = 0;
                    } else {
                        $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                        if ($code == 301 || $code == 302) {
                            preg_match('/Location:(.*?)\n/i', $header, $matches);
                            $newurl = trim(array_pop($matches));

                            // if no scheme is present then the new url is a
                            // relative path and thus needs some extra care
                            if(!preg_match("/^https?:/i", $newurl)){
                              $newurl = $original_url . $newurl;
                            }
                        } else {
                            $code = 0;
                        }
                    }
                } while ($code && --$mr);

                curl_close($rch);

                if (!$mr)
                {
                    if ($maxredirect === null)
                        trigger_error('Too many redirects.', E_USER_WARNING);
                    else
                        $maxredirect = 0;

                        return false;
                }
                curl_setopt($ch, CURLOPT_URL, $newurl);
            }
        }
        return curl_exec($ch);
    }
}
