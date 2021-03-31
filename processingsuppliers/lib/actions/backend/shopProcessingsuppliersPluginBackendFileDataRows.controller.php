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

class shopProcessingsuppliersPluginBackendFileDataRowsController extends waJsonController
{
    const INFINITY = '∞';
    const ARROW_RIGHT = '→';
    const COMMON_STOCK = 'Общий';
    const NOT_EXISTS = 'Нет на сайте';
    const WITH_MARKUP = 'С наценкой';
    const MARKUP_ABSOLUTE = 1;
    const MARKUP_PERCENTAGE = 2;

    protected $fileDataModel;
    protected $skuModel;
    protected $productModel;
    protected $stockModel;
    protected $productStock;
    protected $currencyModel;
    protected $supplier;
    protected $currency;
    protected $settings;
    protected static $allowedMethods = array('get', 'post');
    protected static $allowedEmulatedMethods = array('PUT', 'DELETE');

    public function __construct()
    {
        $this->fileDataModel = new shopProcessingsuppliersFileDataModel();
        $this->skuModel = new shopProductSkusModel();
        $this->productModel = new shopProductModel();
        $this->stockModel = new shopStockModel();
        $this->productStock = new shopProductStocksModel();
        $this->currencyModel = new shopCurrencyModel();

        $fileModel = new shopProcessingsuppliersFileModel();
        $supplierModel = new shopProcessingsuppliersSupplierModel();

        $fileId = waRequest::get('id', 0, waRequest::TYPE_INT);
        $supplierId = $fileModel->getSupplierIdById($fileId);
        $this->supplier = $supplierModel->getById($supplierId);
        $this->settings = shopProcessingsuppliersPlugin::getCorrectSettings($this->supplier);
    }

    private function getPriceWithCurrency($price, $currencyCode)
    {
        return sprintf('%s %s', shopRounding::roundCurrency($price, $currencyCode), $currencyCode);
    }

    private function getPrice($sitePrice, $siteCurrency, $filePrice, $fileCurrency, $property)
    {
        $newPrice = $filePrice;
        if ($property == 'sku@price' && !$this->settings['update_greater_price']) {
            $filePriceConverted = $this->currencyModel->convert($filePrice, $fileCurrency, $siteCurrency);
            if ($sitePrice > $filePriceConverted) {
                $newPrice = $this->currencyModel->convert($sitePrice, $siteCurrency, $fileCurrency);
            }
        }

        return sprintf('%s %s %s', $this->getPriceWithCurrency($sitePrice, $siteCurrency), self::ARROW_RIGHT, $this->getPriceWithCurrency($newPrice, $fileCurrency));
    }

    private function getStock($sku_id, $property, $stockInFile)
    {
        $stock_id = preg_replace('/[^0-9]/', '', $property);

        if ($stock_id) {
            $stockCounts = $this->productStock->getCounts($sku_id);
            if (!$stockCounts) {
                return sprintf('%s %s %s', self::INFINITY, self::ARROW_RIGHT, $stockInFile);
            }

            return sprintf('%s %s %s', $stockCounts[$stock_id], self::ARROW_RIGHT, $stockInFile);
        } else {
            $count = $this->skuModel->select('count')->where('id = i:sku_id', array('sku_id' => $sku_id))->fetchField('count');
            if (empty($count)) {
                return sprintf('%s %s %s', self::INFINITY, self::ARROW_RIGHT, $stockInFile);
            }

            return sprintf('%s %s %s', $count, self::ARROW_RIGHT, $stockInFile);
        }
    }

    private function getParsedColumns()
    {
        $supplierColumnPropertyModel = new shopProcessingsuppliersSupplierColumnPropertyModel();
        $columns = json_decode($this->supplier['columns'], true);
        foreach ($columns as $k => $column) {
            $property = $supplierColumnPropertyModel->getProperty($column['property']);
            $columns[$column['property']] = array_merge($column, $property);
            unset($columns[$k]);
        }

        return $columns;
    }

    private function getOnlyVisibleInUploadTableProperties($fileRowData)
    {
        $columns = $this->getParsedColumns();
        foreach ($fileRowData as $property => $value) {
            if (!$columns[$property]['visibleInUploadTable']) {
                unset($columns[$property]);
            } else {
                $columns[$property]['value'] = $value;
            }
        }

        return $columns;
    }

    public function execute()
    {
        if (waRequest::isXMLHttpRequest()) {
            $method = waRequest::getMethod();
            $emulatedMethod = waRequest::post('_method');

            if (!in_array($method, self::$allowedMethods)) {
                throw new waException('Запрещенный метод');
            }

            if ($emulatedMethod && !in_array($emulatedMethod, self::$allowedEmulatedMethods)) {
                throw new waException('Запрещенный эмулированный метод');
            }

            if ($method == 'get'){
                $id = waRequest::get('id', 0, waRequest::TYPE_INT);
                $offset = waRequest::get('offset', 0, waRequest::TYPE_INT);
                $limit = waRequest::get('limit', 0, waRequest::TYPE_INT);
                if ($id) {
                    $fileDataRows = $this->fileDataModel->getByFileId($id, $offset, $limit);
                    // wa_dump($fileDataRows);
                    foreach ($fileDataRows as &$fileDataRow) {
                        $fileDataRow['data'] = json_decode($fileDataRow['data'], true);
                        $sku = $this->skuModel->getByField('sku', $fileDataRow['data']['sku@sku']);
                        $product = $this->productModel->getById($sku['product_id']);
                        $currency = (isset($fileDataRow['data']['product@currency'])) ? $fileDataRow['data']['product@currency'] : $this->settings['currency'];

                        $fileDataRow['data'] = $this->getOnlyVisibleInUploadTableProperties($fileDataRow['data']);
                        foreach ($fileDataRow['data'] as &$property) {
                            if (!$sku || !$product) {
                                $property['not_exists'] = true;
                            }

                            switch ($property['type']) {
                                case 'string':
                                    if(is_array($property['value'])) {
                                        $str = '';
                                        foreach ($property['value'] as $key => $value) {
                                            if($value != end($property['value'])) {
                                                $str = $str . $value . ",";
                                            } else {
                                                $str = $str . $value;
                                            }

                                        }
                                        $property['value'] = htmlspecialchars_decode($str, ENT_QUOTES);
                                    } else {
                                        $property['value'] = htmlspecialchars_decode($property['value'], ENT_QUOTES);
                                    }
                                    break;
                                case 'price':
                                    if ($property['property'] == 'sku@price') {
                                        if ($this->settings['add_markup']) {
                                            if ($this->settings['markup_type'] == self::MARKUP_ABSOLUTE) {
                                                $property['value'] += $this->settings['markup'];
                                            }

                                            if ($this->settings['markup_type'] == self::MARKUP_PERCENTAGE) {
                                                $property['value'] += $property['value'] / 100 * $this->settings['markup'];
                                            }

                                            $property['name'] = sprintf('%s (%s)', $property['name'], self::WITH_MARKUP);
                                        }
                                    }

                                    $p = explode('@', $property['property']);
                                    $p = $p[1];
                                    if (!empty($property['value'])) {
                                        if ($sku && $product) {
                                            $property['value'] = $this->getPrice($sku[$p], $product['currency'], $property['value'], $currency, $property['property']);
                                        } else {
                                            $property['value'] = $this->getPriceWithCurrency($property['value'], $currency);
                                        }
                                    }
                                    break;
                                case 'stock':
                                    if ($sku && $product) {
                                        $property['value'] = $this->getStock($sku['id'], $property['property'], $property['value']);
                                    }
                                    break;
                                default:
                                    throw new waException(sprintf('Неизвестный тип свойства: %s', $property['type']));
                                    break;
                            }
                        }
                    }

                    $this->response = $fileDataRows;
                    //wa_dump($this->response);

                } else {
                    // get all rows
                }
            }

            if ($method == 'post') {
                if ($emulatedMethod == 'PUT') {
                    // update
                }

                if ($emulatedMethod == 'DELETE') {
                    // delete
                }

                if (!$emulatedMethod) {
                    // create
                }
            }
        }
    }
}