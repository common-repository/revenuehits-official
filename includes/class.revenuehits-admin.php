<?php

require_once "class.revenuehits.helpers.php";

class Revenuehits_Admin
{


    const VERSION = '6.0.0';


    protected $plugin_slug;


    private static $instance;


    protected $templates;

    private $errors = array();

    private $success = array();


    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new Revenuehits_Admin();
        }

        return self::$instance;

    }

    public static function init()
    {
        $class = __CLASS__;
        new $class;
    }

    private function __construct()
    {

        $this->templates = array();


        if (isset($_REQUEST['settings-updated']) && $_REQUEST['settings-updated'] == true) {
            $this->revenue_submit();
        }

        add_action('wp_ajax_get_excluded_posts', array($this, 'revenue_ajax_get_posts'));

        add_action('init', array($this, 'create_settings'));

        add_action('admin_print_scripts', array($this, 'revenue_excluded_posts'));

        add_filter('page_attributes_dropdown_pages_args', array($this, 'register_project_templates'));

        add_filter('wp_insert_post_data', array($this, 'register_project_templates'));

        add_filter('template_include', array($this, 'view_project_template'));

        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_activation_hook(__FILE__, 'create_settings');

        $this->templates = array(
            'template.php' => __('Settings', $this->plugin_slug),
        );

        $templates = wp_get_theme()->get_page_templates();
        $templates = array_merge($templates, $this->templates);


    }

    private function _jsonRemoveUnicodeSequences($struct)
    {
        return utf8_decode(preg_replace("/\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1")).chr(hexdec("$2"))', $struct));
    }

    private function _fixBadUnicodeForJson($str)
    {
        $str = preg_replace("/\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1")).chr(hexdec("$2")).chr(hexdec("$3")).chr(hexdec("$4"))', $str);
        $str = preg_replace("/\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1")).chr(hexdec("$2")).chr(hexdec("$3"))', $str);
        $str = preg_replace("/\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1")).chr(hexdec("$2"))', $str);
        $str = preg_replace("/\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1"))', $str);
        return $str;
    }

    public function revenue_submit()
    {

        $userId = get_option('revenuehits_userid');
        $password = get_option('revenuehits_password');

        if ($password && $userId) {

            add_option('revenuehits_script_footer');
            add_option('revenuehits_script_popunder');
            add_option('revenuehits_script_dialog');
            add_option('revenuehits_script_interstitial');
            add_option('revenuehits_script_shadow_box');
            add_option('revenuehits_script_notifier');
            add_option('revenuehits_script_topbanner');
            add_option('revenuehits_script_float');


            foreach ($GLOBALS['RH_ZONE_TYPES'] as $k => $v) {
                $this->handleTypeSubmission($k, $userId, $password);
            }

            delete_option('revenuehits_password');

            if (!$this->errors) {
                add_action('admin_notices', array($this, 'ads_updated'));
            } elseif ($this->success) {
                add_action('admin_notices', array($this, 'success_updated'));
            }
            add_action('admin_notices', array($this, 'get_option_errors'));
        } elseif
        (!get_option('revenuehits_userid')) {
            add_action('admin_notices', array($this, 'error_userid'));
        } else {
            add_action('admin_notices', array($this, 'settings_updated'));
        }
    }

    private function handleTypeSubmission($zone_type, $pid, $password)
    {
        $type = $GLOBALS['RH_ZONE_TYPES']->$zone_type->name;
        $extraOptionKey = $GLOBALS['RH_ZONE_TYPES']->$zone_type->extra;
        $options = $GLOBALS['RH_ZONE_TYPES']->$zone_type->params;

        add_option('revenuehits_script_' . $type);
        $componentActivated = strtolower(get_option('revenuehits_position_' . $type)) === 'on';

        if ($componentActivated) {
            $urlGenerator = new Revenuehits_UrlGenerator($pid, get_option('revenuehits_script_' . $type));
            $urlGenerator->setOption('type', $zone_type);
            $urlGenerator->setOption('password', $password);
            foreach ($options as $key => $value) {
                $valueToSet = $value;
                if ((!empty($extraOptionKey)) && ($key === $extraOptionKey)) {
                    $extraOptionValue = get_option('revenuehits_extra_' . $type);
                    if (!empty($extraOptionValue)) {
                        $valueToSet = $extraOptionValue;
                    }
                }
                $urlGenerator->setOption($key, $valueToSet);
            }
            $this->_get_revenue_script($urlGenerator, $zone_type);
        }
    }

    public function success_updated()
    {
        $success = '';
        $numItems = count($this->success);
        $i = 0;
        foreach ($this->success as $key => $item) {
            $separator = ++$i === $numItems ? '' : ', ';
            $success .= $item . $separator;
        }
        echo '<div class="updated"><p>' . $success . '</p></div>';
    }

    public function get_option_errors()
    {
        if ($this->errors) {
            $error = '';
            $numItems = count($this->errors);
            $i = 0;
            foreach ($this->errors as $key => $item) {
                $separator = ++$i === $numItems ? '' : ', ';
                $error .= $item . $separator;
            }
            echo '<div class="error"><p>' . $error . '</p></div>';
        }
    }

    public function ads_updated()
    {
        echo "<div class=\"updated\"><p>Ads were successfully updated</p></div>";;
    }

    public function settings_updated()
    {
        echo '<div class="updated"><p>Settings have been successfully saved</p></div>';
    }

    public function error_userid()
    {
        echo '<div class="error"><p>Failed to update the settings, you need to specify the username.</p></div>';
    }

    private function _get_revenue_script($url, $zone_type)
    {
        try {

            $nice_name = $GLOBALS['RH_ZONE_TYPES']->$zone_type->niceName;
            $type = $GLOBALS['RH_ZONE_TYPES']->$zone_type->name;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode !== 200) {
                throw new Exception("Failed to create <strong>$nice_name</strong> - Can't access the API, please try again in a few minutes");
            }
            $http_response_json = json_decode($result, true);
            if (!isset($http_response_json['retCode']) || $http_response_json['retCode'] != 'OK') {
                throw new Exception("Failed to inject <strong>$nice_name</strong> ad type");
            }
            update_option('revenuehits_script_' . $type, $http_response_json['cid']);

            $this->success[] = "<strong>$nice_name</strong> type has been successfully updated";

        } catch (Exception $exp) {
            update_option('revenuehits_script_' . $type, 'Failed');
            $this->errors[] = $exp->getMessage();
        }
    }

    public function revenue_excluded_posts()
    {
        $posts = get_option('revenuehits_exclude_pages');

        if (!$posts) {
            $posts = 'false';
        }

        echo "<script type='text/javascript'> var excludedPosts = $posts; </script>";
    }

    public static function revenue_ajax_get_posts()
    {

        $args = array('posts_per_page' => -1);
        $posts = get_posts($args);

        $postsName = array();
        foreach ($posts as $key => $item) {
            setup_postdata($item);
            $postsName[] = array('name' => $item->post_title, 'id' => $item->ID);
        }

        wp_send_json($postsName);
    }

    public function create_settings()
    {
        /* add_option('revenuehits_show', 1);
         add_option('revenuehits_position_footer', 1);
         add_option('revenuehits_position_popunder', 1);
         add_option('revenuehits_position_dialog', 1);
         add_option('revenuehits_position_notifier', '');
         add_option('revenuehits_position_shadow_box', '');
         add_option('revenuehits_position_interstitial', '');
         add_option('revenuehits_homepage', 1);
         add_option('revenuehits_categories', 1);
         add_option('revenuehits_posts', 1);
         add_option('revenuehits_other', 1);*/

    }

    public static function load_resources()
    {

        wp_enqueue_style('revenuehits', REVENUEHITS__PLUGIN_URL . 'css/revenuehits.css', false, REVENUEHITS_VERSION);

        wp_enqueue_style('textext-core', REVENUEHITS__PLUGIN_URL . 'css/textext.core.css', false, REVENUEHITS_VERSION);

        wp_enqueue_style('textext-arrow', REVENUEHITS__PLUGIN_URL . 'css/textext.plugin.arrow.css', false, REVENUEHITS_VERSION);

        wp_enqueue_style('textext-autocomplete', REVENUEHITS__PLUGIN_URL . 'css/textext.plugin.autocomplete.css', false, REVENUEHITS_VERSION);

        wp_enqueue_style('textext-focus', REVENUEHITS__PLUGIN_URL . 'css/textext.plugin.focus.css', false, REVENUEHITS_VERSION);

        wp_enqueue_style('textext-prompt', REVENUEHITS__PLUGIN_URL . 'css/textext.plugin.prompt.css', false, REVENUEHITS_VERSION);

        wp_enqueue_style('textext-tags', REVENUEHITS__PLUGIN_URL . 'css/textext.plugin.tags.css', false, REVENUEHITS_VERSION);

        wp_enqueue_script('revenue-jquery', REVENUEHITS__PLUGIN_URL . 'js/jquery-2.1.4.min.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-core', REVENUEHITS__PLUGIN_URL . 'js/textext.core.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-ajax', REVENUEHITS__PLUGIN_URL . 'js/textext.plugin.ajax.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-arrow', REVENUEHITS__PLUGIN_URL . 'js/textext.plugin.arrow.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-autocomplete', REVENUEHITS__PLUGIN_URL . 'js/textext.plugin.autocomplete.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-filter', REVENUEHITS__PLUGIN_URL . 'js/textext.plugin.filter.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-focus', REVENUEHITS__PLUGIN_URL . 'js/textext.plugin.focus.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-prompt', REVENUEHITS__PLUGIN_URL . 'js/textext.plugin.prompt.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-suggestions', REVENUEHITS__PLUGIN_URL . 'js/textext.plugin.suggestions.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('textext-tags', REVENUEHITS__PLUGIN_URL . 'js/textext.plugin.tags.js', array('jquery'), REVENUEHITS_VERSION);

        wp_enqueue_script('revenuehits', REVENUEHITS__PLUGIN_URL . 'js/revenuehits.js', array('jquery'), REVENUEHITS_VERSION);
    }

    public function load_plugin_textdomain()
    {

        $domain = $this->plugin_slug;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, trailingslashit(WP_LANG_DIR) . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, basename(dirname(__FILE__)) . '/languages/');

    }

    public function register_project_templates($atts)
    {

        $cache_key = 'revenuehits_templates-' . md5(get_theme_root() . '/' . get_stylesheet());

        $templates = wp_cache_get($cache_key, 'themes');
        if (empty($templates)) {
            $templates = array();
        }

        wp_cache_delete($cache_key, 'themes');

        $templates = array_merge($templates, $this->templates);

        wp_cache_add($cache_key, $templates, 'themes', 1800);

        return $atts;

    }

    /**
     * Checks if the template is assigned to the page
     *
     * @version    1.0.0
     * @since    1.0.0
     */
    public function view_project_template($template)
    {

        global $post;

        if (!isset($post)) return $template;

        if (!isset($this->templates[get_post_meta($post->ID, '_wp_page_template', true)])) {
            return $template;
        }

        $file = plugin_dir_path(__FILE__) . 'templates/' . get_post_meta($post->ID, '_wp_page_template', true);

        if (file_exists($file)) {
            return $file;
        }

        return $template;

    }

    /* static function deactivate($network_wide)
     {
         update_option('revenuehits_show', 2);
         foreach ($this as $value) {
             page - template - example::delete_template($value);
         }
     }

     public
     function delete_template($filename)
     {
         $theme_path = get_template_directory();
         $template_path = $theme_path . '/' . $filename;
         if (file_exists($template_path)) {
             unlink($template_path);
         }
         wp_cache_delete($cache_key, 'themes');
     }*/
}
