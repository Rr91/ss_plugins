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

class shopProcessingsuppliersPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $settings = wa('shop')->getPlugin('processingsuppliers')->getSettings();
        $this->view->assign('settings', $settings);

        $currencyModel = new shopCurrencyModel();
        $currencies = $currencyModel->getCurrencies();
        $this->view->assign('currencies', $currencies);

        $stockModel = new shopStockModel();
        $stocks = $stockModel->getAll();
        $this->view->assign('stocks', $stocks);

        $backendUrl = wa_backend_url();
        $this->view->assign('backendUrl', $backendUrl);
    }
}
