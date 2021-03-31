<?php

class shopRwidjectPlugin extends shopPlugin
{
	public static function rWidjetc($str_ids, $title=false, $width = false){
		$pids = explode(",", str_replace(" ", "", $str_ids));
		$my_collection = new shopProductsCollection($pids);
		$products = $my_collection->getProducts();

		if(!$width){
			$width = "256px";
		}

		$hplugin = wa('shop')->getPlugin('rwidject');
		$view = wa()->getView();
        
        $view->assign('products', $products);
        $view->assign('title', $title);
        $view->assign('width', $width);
        
        return $view->fetch($hplugin->path.'/templates/Toolbar.html');
	}
}
