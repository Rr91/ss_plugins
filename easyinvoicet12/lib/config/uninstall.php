<?php
/**
//Сopyright "WA-Master ©"
//Plugin for framework "Webasyst Shop Script"
//The author of the plugin 'Snooper'- "snooper@ylig.ru"
**/
$path = wa()->getDataPath('data/easyinvoicet12/', true, 'shop', false);
try {
    waFiles::delete($path);
} catch (Exception $ex) {
    waLog::log($ex->getMessage());
}
