<?php

/*
 *
 * Categoryimages plugin for Webasyst framework.
 *
 * @name Categoryimages
 * @author EasyIT LLC
 * @link http://easy-it.ru/
 * @copyright Copyright (c) 2017, EasyIT LLC
 * @version 2.0.5, 2017-03-28
 *
 */

class shopCategoryimagesPlugin extends shopPlugin
{
	/**
	 * Возвращает путь до корневой директории плагина или url до категории внутри нее
	 * @param $dest - папка внутри корневой директории плагина
	 * @return string - путь или url до директории
	 */
	public function getPluginPath($dest)
	{
		$path = 'plugins/categoryimages/';

		if (!isset($dest) || empty($dest)) {
			return wa()->getAppPath($path, 'shop');
		} else {
			return wa()->getAppStaticUrl('shop') . $path . $dest . '/';
		}
	}

	/**
	 * Возвращает html с интерфейсом добавления изображения в хук диалога настройки категорий.
	 * @param $category - Массив данных категории товаров.
	 * @return string $html - html с интерфейсом добавления изображения в категорию.
	 */
	public function backendCategoryDialog($category)
	{
		$model = new shopCategoryimagesModel();
		$categoryImage = $model->getByField('category_id', $category['id']);
		$path = wa()->getDataUrl("categories/{$category['id']}/", true, 'shop');
		$managerLink = wa()->getUrl() . '?action=products#/categoryimages/';

		if ($categoryImage) {
			$show_ajax = 'hide-box';
			$show_picture = '';
			$checked = ($categoryImage['standard_out']) ? "checked" : "";
			$pic_path = $path . $categoryImage['id'] . "." .$categoryImage['ext'];
		} else {
			$show_ajax = '';
			$show_picture = 'hide-box';
			$checked = '';
			$pic_path = '';
		}

		$html = "
			<div class='field catim-field'>
				<div class='name'>" . _wp('Image') . "</div>
				<div class='value'>
					<div class='catim-ajax-menu {$show_ajax}'>
						<div class='goto-manager-btn'>
							". _wp('Open images manager')."
						</div>
						<div class='catim-ajax-box'>
							<div class='catim-ajax-box-input'>
								<img class='catim-ajax-box-icon' src='" . $this->getPluginPath('img') . "/pic-load-ico.png' alt='picture-ico'> 
								<input class='catim-ajax-box-file' type='file' name='category-image' id='category-image' />
								<label for='category-image' class='for-category-image'>
									<strong>". _wp('Choose a file') ."</strong>
									<span class='catim-ajax-box-dragndrop'>". _wp('or drag it here') ."</span>
								</label>
								<div class='catim-ajax-uploading-message'>
								</div>
							</div>
							<input class='catim-ajax-box-catid' type='hidden' name='catId' value='{$category['id']}' id='cat-id'>
						</div>
					</div>	
					
					<div class='catim-picture-menu {$show_picture}'>
						<a data-featherlight='{$pic_path}'>
							<img src='{$pic_path}' id='cat-pic' class='cat-pic-style'>
						</a>
						" . _wp('Default output') . "
						<label class='ios-switcher-box'>
							<input class='ios-switcher green' id='standout_switcher' type='checkbox' {$checked} name='standardOut'>
							<div><div></div></div>
						</label>
						<button id='delete-catpic' class='del-button'>" . _wp("Remove") . "</button>
						<div class='goto-manager-btn' href='{$managerLink}'>
							" . _wp('Open images manager') . "
						</div>
					</div>
				</div> 
			</div>
			<input type='hidden' id='backend-url-prefix' value='". wa()->getUrl() ."'>
			
			<script>
			$(document).ready(function(){
				onCatimCatLoad();
			});
			</script>";

		return $html;
	}

	/**
	 * Выводит изображение категории в стандартном месте темы - хук frontendCategory
	 * @param $category
	 * @return string - картинка категории
	 * @throws waException
	 */
	public function frontendCategory($category)
	{
		$enabled = wa('shop')->getPlugin('categoryimages')->getSettings('enabled');
		$output = wa('shop')->getPlugin('categoryimages')->getSettings('output');
		$outputImg = wa('shop')->getPlugin('categoryimages')->getSettings('outputImg');

		if (!isset($enabled) || empty($enabled)) {
			return "";
		}

		$model = new shopCategoryimagesModel();
		$catImage = $model->getByField('category_id', $category['id']);

		if (!isset($catImage['id']) || empty($catImage['id'])) {
			return "";
		}

		if (isset($outputImg)) {
			switch ($outputImg):
				case('origin'):
					$path = self::getCategoryImageUrl($category['id']);
					break;
				case('big'):
					$path = self::getCategoryBigThumbUrl($category['id']);
					break;
				case('middle'):
					$path = self::getCategoryMiddleThumbUrl($category['id']);
					break;
				case('little'):
					$path = self::getCategoryLittleThumbUrl($category['id']);
					break;
				default:
					$path = self::getCategoryImageUrl($category['id']);
					break;
			endswitch;

			if (isset($path) && !empty($path)) {
				$imgHtml = "<img class='catim-plugin-cat-pic' src='{$path}' alt='image-category-{$category['id']}'>";
			} else {
				return "";
			}

		} else {
			return "";
		}

		if (isset($catImage['standard_out']) && !empty($catImage['standard_out'])) {
			return $imgHtml;
		} elseif (isset($output) && !empty($output)) {
			return $imgHtml;
		} else {
			return "";
		}
	}

	/**
	 * Возвращает изображение категории
	 * @param $catId - id категории
	 * @return bool|string
	 */
	public static function showBackendCategoryImg($catId)
	{
		return '<img src="'.self::getCategoryImageUrl($catId).'" / >';
	}

	/**
	 * Универсальная функция - возвращает url картинки категории, по ее типу
	 * @param $catId - id категории
	 * @param $picType - тип картинки, оригинал или эскиз
	 * @return string
	 */
	private static function getCategoryImgUrlByType($catId, $picType) {
		$model = new shopCategoryimagesModel();
		$categoryImage = $model->getByField('category_id', $catId);
		$urlPath = wa()->getDataUrl("categories/{$catId}/", true, 'shop');
		$serverPath = wa()->getDataPath("categories/{$catId}/", 'public', 'shop');

		if (isset($categoryImage['id']) && !empty($categoryImage['id'])) {
			$picPath = $categoryImage['id'] . $picType . "." . $categoryImage['ext'];
			$serverPicPath = $serverPath . $picPath;
			$urlPicPath = $urlPath . $picPath;

			if (file_exists($serverPicPath)) {
				return $urlPicPath;
			} else {
				return '' ;
			}
		} else {
			return '';
		}
	}

	/**
	 * Добавляет в массив категорий созданный функцией categories класса shopViewHelper, изображения.
	 * @param $array - массив категорий
	 * @param $type - тип вставляемого изображения - url или тег img
	 * @param $picType - тип картинки, оригинал или один из 3 эскизов
	 * @param $imgClass - класс, к тегу img если нужен
	 * @param $recursion - маркер рекурсии
	 * @return array
	 */
	public static function addImagesToCategories($array, $type = 'image', $picType = '', $imgClass = '', $recursion = false) {

		$picTypes = array('big', 'middle', 'little');

		if ($picType != '' && !in_array($picType, $picTypes) && !$recursion) {
			$picType = '';
		} elseif (in_array($picType, $picTypes) && !$recursion) {
			$picType = '_' . $picType;
		}

		foreach ($array as $key => &$value) {
			if ($type == 'url') {
				$value['catim_img'] = self::getCategoryImgUrlByType($value['id'], $picType);
			} else {
				$img = self::getCategoryImgUrlByType($value['id'], $picType);

				if ($img) {
					$value['catim_img'] = "<img class='{$imgClass}' src='{$img}'>";
				} else {
					$value['catim_img'] = '';
				}
			}

			if (isset($value['childs'][0]) && !empty($value['childs'][0])) {
				$value['childs'] = self::addImagesToCategories($value['childs'], $type, $picType, $imgClass, true);
			}
		}

		return $array;
	}

	/**
	 * Возвращает url основной картинки категории
	 * @param $catId - id категории
	 * @return string
	 */
	public static function getCategoryImageUrl($catId)
	{
		$picType = '';
		return self::getCategoryImgUrlByType($catId, $picType);
	}

	/**
	 * Возвращает url большого эскиза картинки категории
	 * @param $catId - id категории
	 * @return string
	 */
	public static function getCategoryBigThumbUrl($catId)
	{
		$picType = '_big';
		return self::getCategoryImgUrlByType($catId, $picType);
	}

	/**
	 * Возвращает url среднего эскиза картинки категории
	 * @param $catId - id категории
	 * @return string
	 */
	public static function getCategoryMiddleThumbUrl($catId)
	{
		$picType = '_middle';
		return self::getCategoryImgUrlByType($catId, $picType);
	}

	/**
	 * Возвращает url маленького эскиза картинки категории
	 * @param $catId - id категории
	 * @return string
	 */
	public static function getCategoryLittleThumbUrl($catId)
	{
		$picType = '_little';
		return self::getCategoryImgUrlByType($catId, $picType);
	}

	/**
	 * Выводит ссылку на менеджер картинок категорий в верхней части левой боковой панели.
	 * Выводит изображение категории справа от заголовка в верхней части экрана «Товары».
	 * @param $category
	 * @return array
	 */
	public function backendProducts($category)
	{
		$menuItem = "<li id='s-categoryimages'>
						<a href='#/categoryimages/'>
							<i class='icon16'
								style='background-image: url(" . $this->getPluginPath('img') . "/icon.png); background-size: 16px 16px;'>
							</i>". _wp('Categories images') ."
                     	</a>
					</li>
					<script type='text/javascript' src='" . $this->getPluginPath('js') . "vendors/featherlight/featherlight.min.js'></script>
					<script type='text/javascript' src='" . $this->getPluginPath('js') . "vendors/tooltipster/tooltipster.bundle.min.js'></script>
					<script type='text/javascript' src='" . $this->getPluginPath('js') . "vendors/velocity/velocity.min.js'></script>
					<script type='text/javascript' src='" . $this->getPluginPath('js') . "filebrowser_init_script.js'></script>
					<script type='text/javascript' src='" . $this->getPluginPath('js') . "cat_dnd_script.js'></script>
					<script type='text/javascript' src='" . $this->getPluginPath('js') . "filebrowser_script.js'></script>
					<link rel='stylesheet' type='text/css' href='" . $this->getPluginPath('css') . "vendors/featherlight/featherlight.min.css'>
					<link rel='stylesheet' type='text/css' href='" . $this->getPluginPath('css') . "vendors/tooltipster/tooltipster.bundle.min.css'>
					<link rel='stylesheet' type='text/css' href='" . $this->getPluginPath('css') . "vendors/tooltipster/themes/tooltipster-shadow.min.css'>
					<link rel='stylesheet' type='text/css' href='" . $this->getPluginPath('css') . "cat_dnd_style.css'>
					<link rel='stylesheet' type='text/css' href='" . $this->getPluginPath('css') . "filebrowser_style.css'>
					";

		$html = '';

		if (isset($category['info']['id']) && !empty($category['info']['id'])) {
			$category_id = $category['info']['id'];
			$imageUrl = self::getCategoryImageUrl($category_id);

			if (!empty($imageUrl)) {
				$html = "<a data-featherlight='".$imageUrl."'>
                    		<img style='width:40px; cursor: pointer;' src='".$imageUrl."' alt=''/>
                		 </a>";
			}
		}

		return array(
			"title_suffix" => $html,
			"sidebar_top_li"  => $menuItem,
		);
	}
}