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

class shopProcessingsuppliersPluginBackendFileReadController extends waLongActionController
{
    protected $file;
    protected $supplier;
    protected $columns = array();
    protected $fileRows = array();
    protected $fileModel;
    protected $fileDataModel;
    protected $supplierModel;
    protected $settings;

    public function __construct()
    {
    	// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
        $this->fileModel = new shopProcessingsuppliersFileModel();
        $this->fileDataModel = new shopProcessingsuppliersFileDataModel();
        $this->supplierModel = new shopProcessingsuppliersSupplierModel();

        $id = waRequest::get('id', 0, waRequest::TYPE_INT);

        $this->file = $this->fileModel->getById($id);
        if (!$this->file) {
            throw new waException('Не найден файл для выгрузки');
        }

        $this->supplier = $this->supplierModel->getById($this->file['supplier_id']);
        if (!$this->supplier) {
            throw new waException('Не найден поставщик файла');
        }
        $this->supplier['columns'] = json_decode($this->supplier['columns'], true);


        $supplierColumnPropertyModel = new shopProcessingsuppliersSupplierColumnPropertyModel();
        foreach ($this->supplier['columns'] as $column) {
            $property = $supplierColumnPropertyModel->getProperty($column['property']);
            if($this->file['extension'] == "xml"){
                $property['column'] = $column['column'];
            }
            else{
                $property['column'] = $this->parseColumn($column['column']);
            }
            $this->columns[$column['property']] = $property;
        }
        $this->settings = shopProcessingsuppliersPlugin::getCorrectSettings($this->supplier);
    }

    private function prepare()
    {
        $filePath = wa()->getDataPath('processingsuppliers', false, 'shop').'/'.$this->file['id'].'.'.$this->file['extension'];
        if ($this->file['extension'] == 'xml') {
            $xmlString = file_get_contents($filePath);
            $xml = new SimpleXMLElement($xmlString);
            $config_arr = explode(";", $this->settings['sheet']);
            $xml_arr = $xml;
            foreach ($config_arr as $code) {
                $xml_arr = $xml_arr->$code;
            }
            foreach ($xml_arr as $row) {
                $rowCells = array();
                foreach ($row as $key => $cell) {
                    $rowCells[$key] = (string)$cell; 
                }
                array_push($this->fileRows, $rowCells);
            }
        }
        else{
            waAutoload::getInstance()->add(
                array(
                    'PHPExcel' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/PHPExcel.php',
                    'PHPExcel_IOFactory' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/PHPExcel/IOFactory.php',
                    'PHPExcel_CachedObjectStorageFactory' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/PHPExcel/CachedObjectStorageFactory.php',
                    'PHPExcel_Settings' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/PHPExcel/Settings.php',
                    'chunkReadFilter' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/chunkReadFilter.php'
                )
            );

            $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
    		$cacheSettings = array( ' memoryCacheSize ' => '32MB');
    		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

            $fileType = PHPExcel_IOFactory::identify($filePath);
            if($fileType == 'HTML') {
            	$fileType = 'CSV';
            }
            $objReader = PHPExcel_IOFactory::createReader($fileType);
            $limit = (int)$this->settings['limit'];
            $chunkFilter = new chunkReadFilter();

            if ($this->file['extension'] == 'csv') {
                $objReader->setDelimiter($this->settings['delimiter']);
            }

            $activeSheet = '';

            $firstRow = (int)$this->data['offset'];

    	    $chunkFilter->setRows($firstRow,$limit);
    	    $objReader->setReadFilter($chunkFilter);
    	    $objReader->setReadDataOnly(true);
    	    $objPHPExcel = $objReader->load($filePath);

    	    unset($objReader);
        	$activeSheet = $objPHPExcel->getSheetByName($this->settings['sheet']); // ищем лист по имен
            if (!$activeSheet) {
                $sheet = (int)$this->settings['sheet'];
                $activeSheet = $objPHPExcel->getSheet($sheet); // если по имени не нашли, то пробуем найти по номеру
            }

            if (!$activeSheet) {
                $activeSheet = $objPHPExcel->getSheet(0); // если по имени и номеру не нашли лист, то ставим по дефолту первый лист
            }

            unset($objPHPExcel);

    	    foreach ($activeSheet->getRowIterator($firstRow) as $row) {
                $empty_row = true;
                $rowCells = array();

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    array_push($rowCells, $cell->getFormattedValue());
                    if($cell->getFormattedValue()){
                        $empty_row = false;
                    }
                }
                if(!$empty_row){
                	array_push($this->fileRows, $rowCells);
                } else {
                    // $index = $row->getRowIndex();
                    // $activeSheet->removeRow($index,1);
                }
    	    }

            unset($activeSheet);
        }
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
        $this->data['timestamp'] = time();
        $this->data['offset'] = $this->supplier['first_row'];
        $this->data['total_count'] = $this->getTotalCount();
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
        $response['progress'] = (($this->data['offset'] - $this->supplier['first_row']) / ($this->data['total_count'] - $this->supplier['first_row'])) * 100;
        if($response['progress'] > 100)
        {
            $response['progress'] = 100;
        }
        $response['progress'] = sprintf('%0.3f%%', $response['progress']);

        echo json_encode($response);
    }

    public function step()
    {
        $this->prepare();

        $rows = $this->fileRows;
        $data = array();
        foreach ($rows as $row) {
            $row = $this->escape($row);
            if($this->supplier['postfix'])
                $row[$this->columns['sku@sku']['column']] = $row[$this->columns['sku@sku']['column']]."__".$this->supplier['postfix'];
            $sku=1;$category_id=1;$sku_parent=1;$name=1;

            if(isset($this->columns['sku@sku'])){
                if(!empty($row[$this->columns['sku@sku']['column']])){
                    $sku=0;
                }
            }
            
            if(isset($this->columns['product@category_id'])){
                if(!empty($row[$this->columns['product@category_id']['column']])){
                    $category_id=0;
                }
            }
            
            if(isset($this->columns['sku@sku_parent'])){
                if(!empty($row[$this->columns['sku@sku_parent']['column']])){
                    $sku_parent=0;
                }
            }
            
            if(isset($this->columns['product@name'])){
                if(!empty($row[$this->columns['product@name']['column']])){
                    $name=0;
                }
            }


            if($sku){
                continue;
            }

            $data = array(
                'file_id' => $this->file['id']
            );
            $rowData = array();
            foreach ($this->columns as $property) {
                if(strpos($property['column'], ",")) {
                    $result = explode(",", $property['column']);
                    $base_str = "";
                    $first_img = "";
                    foreach ($result as $key => &$value) {
                        if(strpos($value, "*")) {
                            $value_new = str_replace("*", "", $value);
                            if(preg_match('/(http:\/\/.+[\.com|\.ru])\/.+\.Jpg/', $this->escapeString($row[(int)$value_new-1]), $matches)) {
                                $base_str = $matches[1];
                                $first_img = $matches[0];
                            } else {
                                $base_str = $this->escapeString($row[(int)$value_new-1]);
                            }
                            continue;
                        }
                        if($base_str != "") {
                            if(strpos($this->escapeString($row[(int)$value-1]), ",")) {
                                $array_value = explode(",", $this->escapeString($row[(int)$value-1]));
                                foreach ($array_value as $key => &$value) {
                                    $value = $base_str . $value;
                                }
                                if($first_img != "") {
                                    $array_value[] = $first_img;
                                }
                                $array_value = implode(",", $array_value);
                                $rowData[$property['property']][] = $array_value;
                            } else {
                                if($this->escapeString($row[(int)$value-1]) != "") {
                                    $rowData[$property['property']][] = $base_str . $this->escapeString($row[(int)$value-1]);
                                }
                                if($first_img != "") {
                                    $rowData[$property['property']][] = $first_img;
                                }
                            }
                        } else {
                            $rowData[$property['property']][] = $this->escapeString($row[(int)$value-1]);
                        }
                    }
                } else {
                    // wa_dumpc($property, $row);
                    $rowData[$property['property']] = $row[$property['column']];
                }
                // wa_dumpc($property, $row);
            }
            
            $rowData = json_encode($rowData);


            $data['data'] = $rowData;

            $this->fileDataModel->insert($data);
        }

        $this->data['offset'] += $this->settings['limit'];
    }

    public function isDone()
    {
        return $this->data['offset'] >= $this->data['total_count'];
    }

    public function finish($filename)
    {
        $this->info();

        if($this->isDone()) {
            return true;
        } else {
            return false;
        }
    }

    private function parseColumn($value)
    {
        if(preg_match('/-|,/', $value)) {
            $result = array();
            while (preg_match('/([0-9]+)-([0-9]+)/', $value, $result)) {
                $str = '';
                for ($i=$result[1]; $i < ($result[2]+1); $i++) {
                    if($i == $result[2]) {
                        $str = $str . $i;
                    } else {
                        $str = $str . $i . ",";
                    }
                }
                $value = preg_replace("/" . $result[1] . "-" . $result[2] . "/", $str, $value);
            }
            return $value;
        }
        $value = strtoupper($value);
        if (!(int)$value) {
            $charCode = ord($value);
            if ($charCode >= 65 && $charCode <= 90) {
                return $charCode - 65;
            } else {
                throw new waException(sprintf('Неправильный символ: %s', $value));
            }
        } else {
            return $value - 1;
        }
    }

    private function escapeString($string)
    {
        return $this->supplierModel->escape(trim(htmlspecialchars($string, ENT_QUOTES))); // кодируем специальные символы, включая кавычки, обрезаем пробелы по краям и эскейпим
    }

    private function escapePrice($price)
    {
        $price = preg_replace('/[^0-9.,]/', '', $price); // оставляем в строке с ценой только числа, точки и запятые, чтобы исключить возможность попадания невидимых/особых символов
        preg_match('/[\d]+[,.]*[\d]+/', $price, $matches); // приводим цену к валидному формату
        $escapedPrice = array_shift($matches);
        $escapedPrice = str_replace(',', '.', $escapedPrice);

        return $escapedPrice;
    }

    private function escapeStock($stock)
    {
    	$side = 'more';
        // $stock = str_replace("больше", "", $stock);
        // $stock = str_replace("меньше", "", $stock);
        // if($stock){
        //     wa_dump($stock);
        // }
        if ($stock == 'Есть в наличии' || $stock == 'Есть' || $stock == 'есть' || $stock == 'Да' || $stock == 'да' || $stock == 'PPP' || $stock == 'PP' || $stock == 'P' || $stock == 'в наличии') {
            $stock = NULL;
        } elseif (strpos($stock, "-")) {
            $res = explode("-", $stock);
            if($side == 'more'){
                $stock = $res[1];
            }else {
                $stock = $res[0];
            }
        } else {
            if(intval($stock) < 0) $stock = 0;
            $stock = preg_replace('/\..+|,.+/', '', $stock); // оставляем в остатках только циф
            $stock = preg_replace('/[^0-9]/', '', $stock); // оставляем в остатках только цифры
            $stock = (int)$stock; // остатки могут быть с запятыми или точками, поэтому отбрасываем дробную часть
        }
        return $stock;
    }

    private function escape($row)
    {
        foreach ($this->columns as $property) {
            switch ($property['type']) {
                case 'string':
                    if(strpos($property['column'], ",")) {
                        $result = explode(",", $property['column']);
                        foreach ($result as $key => &$value) {
                            $row[(int)$value] = $this->escapeString($row[(int)$value]);
                        }
                    } else {
                        $row[$property['column']] = $this->escapeString($row[$property['column']]);
                    }

                    break;
                case 'price':
                    $row[$property['column']] = $this->escapePrice($row[$property['column']]);
                    break;
                case 'stock':
                    $row[$property['column']] = $this->escapeStock($row[$property['column']]);
                    break;
                default:
                    throw new waException(sprintf('Неизвестный тип свойства: %s', $property['type']));
                    break;
            }
        }

        return $row;
    }

    private function getTotalCount() {
         $filePath = wa()->getDataPath('processingsuppliers', false, 'shop').'/'.$this->file['id'].'.'.$this->file['extension'];
         waAutoload::getInstance()->add(
            array(
                'PHPExcel' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/PHPExcel.php',
                'PHPExcel_IOFactory' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/PHPExcel/IOFactory.php',
                'PHPExcel_CachedObjectStorageFactory' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/PHPExcel/CachedObjectStorageFactory.php',
                'PHPExcel_Settings' => 'wa-apps/shop/plugins/processingsuppliers/lib/vendors/PHPExcel/Settings.php'
            )
        );

        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '64MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $fileType = PHPExcel_IOFactory::identify($filePath);
        if($fileType == 'HTML') {
        	$fileType = 'CSV';
        }
        $objReader = PHPExcel_IOFactory::createReader($fileType);

        if ($this->file['extension'] == 'csv') {
            $objReader->setDelimiter($this->settings['delimiter']);
        }

        $objPHPExcel = $objReader->load($filePath);
        $activeSheet = $objPHPExcel->getSheetByName($this->settings['sheet']); // ищем лист по имени
        if (!$activeSheet) {
            $sheet = (int)$this->settings['sheet'];
            $activeSheet = $objPHPExcel->getSheet($sheet); // если по имени не нашли, то пробуем найти по номеру
        }

        if (!$activeSheet) {
            $activeSheet = $objPHPExcel->getSheet(0); // если по имени и номеру не нашли лист, то ставим по дефолту первый лист
        }

        $lastRow = $activeSheet->getHighestRow();

        unset($activeSheet);
        unset($objPHPExcel);
        unset($objReader);

        return $lastRow;
    }
}
