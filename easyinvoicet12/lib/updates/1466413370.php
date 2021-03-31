<?php
$path = wa()->getConfig()->getAppConfig('shop');
$path = $path->getPluginPath('easyinvoicenua');

waFiles::delete($path."/templates/fileControlStamp.html", true);
waFiles::delete($path."/templates/feedbackControl.html", true);
waFiles::delete($path."/templates/fileControlFaximile.html", true);
waFiles::delete($path."/templates/fileControlPageTemplate.html", true);
waFiles::delete($path."/js/editform.js", true);
waFiles::delete($path."/js/version.js", true);
waFiles::delete($path."/lib/config/settings.php", true);
waFiles::delete($path."/img/easyinvoicet12.png", true);