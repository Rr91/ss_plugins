<?php

/**
 * Class shopSeoreviewsPluginTemplates
 */
class shopSeoreviewsPluginTemplates {

    /**
     * Массив ключей и названий файлов плагина в темах дизайна
     * @var array
     */
    protected static $theme_templates = array(
        'horizontal' => 'plugin.seoreviews.horizontal.html',
        'vertical' => 'plugin.seoreviews.vertical.html',
        'css' =>     'plugin.seoreviews.css'
    );
    /**
     *  Массив ключей и названий файлов плагина для показа на витрине
     * @var array
     */
    protected static $plugin_templates = array(
        'horizontal' => 'seoreviews.horizontal.html',
        'vertical' => 'seoreviews.vertical.html',
    );

    /**
     * Пул подготовленных шаблонов плагина
     * @var array
     */
    protected static $templates = array();
    /**
     * Объект Темы дизайна
     * @var null| waTheme
     */
    protected static $theme = null;
    /**
     * Глобальные настройки плагина
     * @var array
     */
    protected $settings = null;

    /**
     * shopSeoreviewsPluginTemplates constructor.
     * @param null $settings
     */
    public function __construct($settings = null)
    {
        // Пишем настройки
        if(!is_array($settings) || !array_key_exists('template_type',$settings)) {
            $settings = array('template_type' => 'theme');
        }
        $this->settings = $settings;
    }

    /**
     * Возвращает подготовленый шаблон по ключу для дальнейшей обработки смарти
     * @param $name
     * @return mixed
     */
    public function getTemplate($name) {
        if(!isset(self::$templates[$name])) {
            if($name == 'css' ||  $name =='js') {
                self::$templates[$name] = '';
                if($this->settings['template_type'] == 'theme') {
                    $template_theme = self::getThemeTemplatePath(self::getTemplateFileName($name, true));
                    if ($template_theme) {
                        $theme = self::getTheme();
                        if($name == 'js') {
                            self::$templates[$name] =  '<script type="text/javascript" src="'. $theme->getUrl().''.self::getTemplateFileName($name, true).'"></script>';
                        } elseif($name == 'css') {
                            self::$templates[$name] =  '<link href="'.$theme->getUrl().''.self::getTemplateFileName($name, true).'" type="text/css" rel="stylesheet">';
                        }
                    }
                }
            } else {
                self::$templates[$name] = 'string: ';
                $template_theme = self::getThemeTemplatePath(self::getTemplateFileName($name, true));
                $template_plugin = self::getPluginTemplatePath('actions/frontend/'.self::getTemplateFileName($name));
                if($this->settings['template_type'] == 'theme') {
                    if(!$template_theme) {
                        $template_theme = $template_plugin;
                    }
                    if ($template_theme) {
                        self::$templates[$name] = 'file:'.$template_theme;
                    }
                } else {
                    if ($template_plugin) {
                        self::$templates[$name] = 'file:'.$template_plugin;
                    }
                }
            }
            
        }
        return  self::$templates[$name];
    }

    

    /**
     * Проверяет существуют ли файлы шаблонов плагина в темах дизайна витрины
     * @return bool
     */
    public function themeTemplatesExists() {
        $themes = shopSeoreviewsPluginStorefronts::getStorefront()->getThemes();
        if(!empty($themes)) {
            foreach ($themes as $type => $theme) {
                if ($theme) {
                    foreach ($this->getThemeTemplates() as $t_key => $filename){
                        if (!self::getThemeTemplatePath($filename, $theme)) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Возвращает полный файловый путь к файлу шаблона плагина по его ключу, либо из плагина, либо из темы
     * @param $name
     * @return bool|string
     */
    public static function getTemplatePath($name) {
        $template = self::getThemeTemplatePath(self::getTemplateFileName($name, true));
        if ($template) {
           return $template;
        } else {
            $template = self::getPluginTemplatePath('actions/frontend/'.self::getTemplateFileName($name));
            if(file_exists($template)) {
                return $template;
            } else {
               return false;
            }
        }
    }

    /**
     * Возвращает абсолютный путь к файлам шаблонов плагина
     * @param string $path
     * @return string
     */
    public static function getPluginTemplatePath($path = '') {
        $path = ltrim($path,'/\\');
        return  realpath(dirname(__FILE__) .'/../../templates/').DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Возвращает абсолютный путь к файлам шаблонов темы дизана
     * @param string $path
     * @param null $theme
     * @return bool|string
     */
    public static function getThemeTemplatePath($path = '', $theme = null) {
        if(!is_object($theme) || !($theme instanceof waTheme)) {
            $theme = self::getTheme();
        }
        $theme_file = $theme->getPath().DIRECTORY_SEPARATOR.$path;
        if (file_exists($theme_file)) {
            return $theme_file;
        }
        return false;
    }

    /**
     * Возвпращает объект темы дизайна для текущей витрины
     * @return null|waTheme
     */
    public static function getTheme()
    {
        if (self::$theme == null) {
            self::$theme = new waTheme(waRequest::getTheme());
        }
        return self::$theme;
    }

    /**
     * Возвращает название файла шаблона по его идентифиткатору (ключу), либо для файлов темы дизайна, либо для файлов плагина
     * @param string $name
     * @param bool $is_theme
     * @return bool|mixed
     */
    public static function getTemplateFileName($name = '', $is_theme = false) {
        $templates = $is_theme? self::getThemeTemplates() : self::$plugin_templates;
        if(isset($templates[$name])) {
            return $templates[$name];
        } elseif($is_theme) {
            return 'plugin.'.shopSeoreviewsPlugin::PLUGIN_ID.'.'.strtolower($name).'.html';
        }
        return false;
    }

    /**
     * Возвращает все назвыания файлов шаблонов для темы дизайна
     * @return array
     */
    public static function getThemeTemplates() {
        return self::$theme_templates;
    }

    /**
     * Возвращает код файла щаблона плагина по его ключу
     * @param $name
     * @return bool|string
     */
    public static function getPluginTemplateContent($name) {
        $template_plugin = self::getTemplateFileName($name);
        if($template_plugin) {
            $template_plugin = self::getPluginTemplatePath('actions/frontend/'.$template_plugin);
            return file_get_contents($template_plugin);
        }

        return '';
    }
}