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

class shopProcessingsuppliersPluginBackendSetupAction extends waViewAction
{
	public function execute()
	{
        $ROOT = waRequest::server('DOCUMENT_ROOT').wa_url();
        $this->view->assign('ROOT', $ROOT);

        $PLUGIN_DIR = 'wa-apps/shop/plugins/processingsuppliers/';
        $this->view->assign('PLUGIN_DIR', $PLUGIN_DIR);

        // preload file models
        $filesModel = new shopProcessingsuppliersFileModel();
        $files = $filesModel->all();
        $this->view->assign('files', json_encode($files));

        // preload supplier models
        $supplierModel = new shopProcessingsuppliersSupplierModel();
        $suppliers = $supplierModel->all();
        $this->view->assign('suppliers', json_encode($suppliers));

        // currencies for templates
        $currencyModel = new shopCurrencyModel();
        $currencies = $currencyModel->getCurrencies();
        $this->view->assign('currencies', $currencies);

        // stocks for templates
        $stockModel = new shopStockModel();
        $stocks = $stockModel->getAll();
        $this->view->assign('stocks', $stocks);

        // features for templates
        $featureModel = new shopFeatureModel();
        $features = $featureModel->getFeatures(true);
        $this->view->assign('features', $features);

        // collect templates for autoloader
        $templates = scandir($ROOT.$PLUGIN_DIR.'templates/actions/backend');
        $templates = array_slice($templates, 2); // remove from array values [.] and [..]
        $this->removeFromArray('BackendSetup.html', $templates); // remove from array [BackendSetup.html] template
        $this->view->assign('templates', $templates);

        // collect js custom modules
        $modules = scandir($ROOT.$PLUGIN_DIR.'js/plugin/modules');
        $modules = array_slice($modules, 2); // remove from array values [.] and [..]
        $this->removeFromArray('files.js', $modules); // remove core module
        $this->removeFromArray('suppliers.js', $modules); // remove core module
        foreach ($modules as &$module) {
            $module = substr($module, 0, strlen($module) - 3); // remove .js from module name
        }
        $this->view->assign('modules', json_encode($modules));

        // supplier column properties
        $supplierColumnPropertyModel = new shopProcessingsuppliersSupplierColumnPropertyModel();
        $supplierColumnProperties = $supplierColumnPropertyModel->all();
        $this->view->assign('supplierColumnProperties', $supplierColumnProperties);
	}

    private function removeFromArray($needle, &$array, $all = true)
    {
        if (!$all) {
            if (false !== $key = array_search($needle, $array)) {
                unset($array[$key]);
            }

            return;
        }

        foreach (array_keys($array, $needle) as $key) {
            unset($array[$key]);
        }
    }
}
