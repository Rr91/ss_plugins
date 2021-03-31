<?php
class shopCsvcliPluginBackendSetupAction extends shopCsvProductsetupAction {

    private $reader;

    private $data_path;

    public function execute(){

        $_POST['direction'] = 'import';

        parent::execute();

        $profile_id      = waRequest::request('profile', 0, waRequest::TYPE_INT);
        $this->data_path = wa()->getDataPath('plugins/csvcli/' . $profile_id . '/', true, 'shop', true);

        $data_path = $this->dataPath();
        $data      = file_exists($data_path) ? include($data_path) : array();
        $csv_path  = $this->csvPath();

        $root_path = wa()->getConfig()->getPath('root');
        $this->view->assign('root_path', $root_path);

        $helper   = new shopImportexportHelper('csvcli');
        $profiles = $helper->getList();

        $this->view->assign('profiles', $profiles);

        $view_data = array();

        $default_name = $this->getDefaultName();
        $this->view->assign('default_name', $default_name);

        if ( $data && file_exists($csv_path) ){
            $this->reader = new shopCsvReader($csv_path, $data['delimiter'], 'auto');

            $view_data['result'] = $this->getData($data);

            $profile = array('config' => array(
                'encoding'  => $data['encoding'],
                'delimiter' => $data['delimiter']
            ));

            $this->view->assign('profile', $profile);
        }

        $this->view->assign('settings', $data);
        $this->view->assign('view_data', $view_data);
        $this->view->assign('profile_id', $profile_id);
    }

    public function getDefaultName(){
        $path = wa()->getDataPath('plugins/csvcli/0/', false, 'shop', true) . 'sets.php';
        $data = file_exists($path) ? include($path) : array();
        return ifempty($data['profile_name'],'Без названия');
    }

    protected function csvPath(){
        return $this->data_path . 'csvcli.csv';
    }

    protected function dataPath(){
        return  $this->data_path . 'sets.php';
    }

    public function getData($data){
        $params = array();
        $params['id'] = 'csvproducts';
        $params['title_wrapper'] = '%s';
        $params['description_wrapper'] = '<br><span class="hint">%s</span>';
        $params['control_wrapper'] = '<div class="field"><div class="name">%s</div><div class="value">%s %s</div></div>';
        $params['options'] = $this->options();
        $control = true ? shopCsvReader::TABLE_CONTROL : shopCsvReader::MAP_CONTROL;

        switch ($control) {
            case shopCsvReader::TABLE_CONTROL:
                $params['preview'] = 50;
                $params['columns'] = array(
                    array('shopCsvProductviewController', 'tableRowHandler'),
                    '&nbsp;',
                );

                $params['control_wrapper'] = '<div class="field"><div class="value" style="overflow-x:auto;margin-left:0;">%s %s</div></div>';
                $params['title_wrapper'] = false;
                $params['row_handler'] = 'csv_product/rows/';
                $params['row_handler_string'] = true;
                $params['autocomplete_handler'] = 'csv_product/autocomplete/reset/';
                break;
            case shopCsvReader::MAP_CONTROL:
            default:
                $control = shopCsvReader::MAP_CONTROL;
                break;
        }

        return array(
            'files' => array(
                array(
                    'name'           => $data['file'],
                    'original_name'  => $data['file'],
                    'size'           => waFiles::formatSize($this->reader->size()),
                    'original_size'  => waFiles::formatSize($this->reader->size()), //waFiles::formatSize($file->size),
                    'controls'       => waHtmlControl::getControl($control, 'csv_map', $params),
                    'control'        => $control,
                    'header'         => $this->reader->header(),
                    'columns_offset' => count(ifset($params['columns'], array())),
                    'delimiter'      => $data['delimiter'],
                    'encoding'       => $data['encoding']
                )
            )
        );
    }

    public static function getMapFields($flat = false, $extra_fields = false)
    {
        $fields = array(
            'product'               => array(
                'name'             => _w('Product name'), //1
                'currency'         => _w('Currency'), //4
                'summary'          => _w('Summary'),
                'description'      => _w('Description'),
                'badge'            => _w('Badge'),
                'status'           => _w('Status'),
                //'sort'             => _w('Product sort order'),
                'type_name'        => _w('Product type'),
                'tags'             => _w('Tags'),
                'tax_name'         => _w('Taxable'),
                'meta_title'       => _w('Title'),
                'meta_keywords'    => _w('META Keyword'),
                'meta_description' => _w('META Description'),
                'url'              => _w('Storefront link'),
                'images'           => _w('Product images'),
                'video_url'        => _w('Video URL on YouTube or Vimeo'),
                //   'rating'                 => _w('Rating'),
                'params'           => _w('Custom parameters'),
            ),
            'product_custom_fields' => array(),
            'sku'                   => array(
                'skus:-1:name'           => _w('SKU name'), //2
                'skus:-1:sku'            => _w('SKU code'), //3
                'skus:-1:price'          => _w('Price'),
                'skus:-1:available'      => _w('Available for purchase'),
                'skus:-1:compare_price'  => _w('Compare at price'),
                'skus:-1:purchase_price' => _w('Purchase price'),
                'skus:-1:stock:0'        => _w('In stock'),
                'skus:-1:_primary'        => _w('Primary SKU'),
            ),
            'sku_custom_fields'     => array(),
        );

        if ($extra_fields) {
            $product_model = new shopProductModel();
            $sku_model = new shopProductSkusModel();
            $meta_fields = array(
                'product' => $product_model->getMetadata(),
                'sku'     => $sku_model->getMetadata(),
            );
            $black_list = array(
                'id',
                'contact_id',
                'create_datetime',
                'edit_datetime',
                'type_id',
                'image_id',
                'image_filename',
                'tax_id',
                'cross_selling',
                'upselling',
                'total_sales',
                'sku_type',
                'sku_count',
                'sku_id',
                'ext',
                'price',
                'compare_price',
                'min_price',
                'max_price',
                'count',
                'rating_count',
                'category_id',
                'base_price_selectable',
                'compare_price_selectable',
                'purchase_price_selectable',
                'rating',
            );

            $white_list = array(
                'id_1c' => 'Идентификатор 1С',
            );

            //XXX add callback for custom fields

            //TODO implode
            foreach ($meta_fields['product'] as $field => $info) {
                if (!in_array($field, $black_list)) {
                    $name = ifset($white_list[$field], $field);
                    if (!empty($meta_fields['sku'][$field])) {
                        if (!isset($fields['sku']['skus:-1:'.$field])) {
                            $fields['sku']['skus:-1:'.$field] = $name;
                        }

                        if (!isset($fields['product'][$field])) {
                            $fields['product'][$field] = sprintf('%s: %s', _w('Product'), $name);
                        }

                    } else {
                        if (!isset($fields['product'][$field])) {
                            $fields['product'][$field] = $name;
                        }
                    }
                }
            }
        }

        $stock_model = new shopStockModel();
        $stocks = $stock_model->getAll('id');
        if ($stocks) {
            foreach ($stocks as $stock_id => $stock) {
                $fields['sku']['skus:-1:stock:'.$stock_id] = _w('In stock').' @'.$stock['name'];
            }
        }

        /**
         * @event product_custom_fields
         * @return array[string]string $return[%plugin_id%]['product'] array
         * @return array[string]string $return[%plugin_id%]['sku'] array
         */
        $custom_fields = wa('shop')->event('product_custom_fields');
        if ($custom_fields) {
            foreach ($custom_fields as $plugin_id => $custom_plugin_fields) {
                # %plugin_id%-plugin became %plugin_id%_plugin
                $plugin_id = preg_replace('@-plugin$@', '_plugin', $plugin_id);
                if (isset($custom_plugin_fields['product'])) {
                    foreach ($custom_plugin_fields['product'] as $field_id => $field_name) {
                        $fields['product_custom_fields'][$plugin_id.':'.$field_id] = $field_name;
                    }
                }
                if (isset($custom_plugin_fields['sku'])) {
                    foreach ($custom_plugin_fields['sku'] as $field_id => $field_name) {
                        $fields['sku_custom_fields']['skus:-1:'.$plugin_id.':'.$field_id] = $field_name;
                    }
                }
            }
        }


        if ($flat) {
            $fields_ = $fields;
            $fields = array();
            $flat_order = array(
                'product:name',
                'sku:skus:-1:name',
                'sku:skus:-1:sku',
                'product:currency'
            );

            foreach ($flat_order as $field) {
                list($type, $field) = explode(':', $field, 2);
                $fields[$field] = $fields_[$type][$field];
                unset($fields_[$type][$field]);
            }
            $fields += $fields_['sku'];
            $fields += $fields_['product'];
        }

        return $fields;

    }


    private function options(){
        $multiple = true;

        $translates = array();
        $translates['product'] = _w('Basic fields');
        $translates['product_custom_fields'] = _w("Custom product fields");
        $translates['sku'] = _w('SKU fields');
        $translates['sku_custom_fields'] = _w("Custom sku fields");

        $translates['feature'] = _w('Add to existing');
        $translates['feature+'] = _w('Add as new feature');


        $options = array();
        $fields = self::getMapFields(false, true);
        foreach ($fields as $group => $group_fields) {
            foreach ($group_fields as $id => $name) {
                $option = array(
                    'group' => array(
                        'title' => ifset($translates[$group]),
                        'class' => $group,
                    ),
                    'value' => $id,
                    'title' => ifempty($name, $id),
                );
                if (preg_match('@^[a-z][a-z0-9_]+$@', $option['title'])) {
                    $option['no_match'] = true;
                    $option['title'] = $option['title'].' *';
                }
                $options[] = $option;
            }
        }

        $limit = $this->getConfig()->getOption('features_per_page');
        $group = 'feature';
        $auto_complete = false;
        $feature_model = new shopFeatureModel();
        if ($feature_model->countByField(array('parent_id' => null)) < $limit) {
            $features = $feature_model->getFeatures(true); /*, true*/
        } else {
            $auto_complete = true;
            $header = array_unique(array_map('mb_strtolower', $this->reader->header()));
            //XXX optimize it for big tables
            $header = array_slice($header, 0, $limit);
            $features = $feature_model->getFeatures('name', $header);
        }
        foreach ($features as $id => $feature) {
            if ($feature['type'] == shopFeatureModel::TYPE_DIVIDER) {
                unset($features[$id]);
            }
        }

        foreach ($features as $code => $feature) {
            $code = $feature['code'];
            if (
                !preg_match('/\.\d$/', $code)
                &&
                ($feature['type'] != shopFeatureModel::TYPE_DIVIDER)
            ) {
                $options[] = array(
                    'group'       => array(
                        'title' => ifset($translates[$group]),
                        'class' => $group,
                    ),
                    'value'       => sprintf('features:%s', $code),
                    'title'       => $feature['name'],
                    'description' => $code,
                );
            }
        }

        if ($auto_complete) {
            $options['autocomplete'] = array(
                'group'    => array(
                    'title' => ifset($translates[$group]),
                    'class' => $group,
                ),
                'value'    => 'features:%s',
                'title'    => _w('Select feature'),
                'callback' => array(),
                'no_match' => true,
            );
        }

        if ($this->getUser()->getRights('shop', 'settings')) {
            $group = 'feature+';
            foreach (shopFeatureModel::getTypes() as $f) {
                if (
                    $f['available']
                    &&
                    ($f['type'] != shopFeatureModel::TYPE_DIVIDER)
                ) {
                    if (empty($f['subtype'])) {
                        if ($multiple || (empty($f['multiple']) && !preg_match('@^(range|2d|3d)\.@', $f['type']))) {
                            $options[] = array(
                                'group'    => & $translates[$group],
                                'value'    => sprintf("f+:%s:%d:%d", $f['type'], $f['multiple'], $f['selectable']),
                                'title'    => empty($f['group']) ? $f['name'] : ($f['group'].': '.$f['name']),
                                'no_match' => true,
                            );
                        }
                    } else {
                        foreach ($f['subtype'] as $sf) {
                            if ($sf['available']) {
                                $type = str_replace('*', $sf['type'], $f['type']);
                                if ($multiple || (empty($f['multiple']) && !preg_match('@^(range|2d|3d)\.@', $type))) {
                                    $options[] = array(
                                        'group'    => & $translates[$group],
                                        'value'    => sprintf("f+:%s:%d:%d", $type, $f['multiple'], $f['selectable']),
                                        'title'    => (empty($f['group']) ? $f['name'] : ($f['group'].': '.$f['name']))." — {$sf['name']}",
                                        'no_match' => true,
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }

        return $options;
    }

}