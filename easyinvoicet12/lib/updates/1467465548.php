<?php
$path = wa()->getConfig()->getAppConfig('shop');
$path = $path->getPluginPath('easyinvoicet12');

waFiles::delete($path."/js", true);