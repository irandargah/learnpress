<?php
/**
 * Plugin load class.
 *
 * @author   IranDargah.com
 * @package  LearnPress/IranDargah/Classes
 * @version  1.0.1
 */

// Prevent loading this file directly
defined('ABSPATH') || exit;

if (!class_exists('LP_Addon_IranDargah_Payment')) {
    /**
     * Class LP_Addon_IranDargah_Payment
     */
    class LP_Addon_IranDargah_Payment extends LP_Addon
    {

        /**
         * @var string
         */
        public $version = LP_ADDON_IRANDARGAH_PAYMENT_VER;

        /**
         * @var string
         */
        public $require_version = LP_ADDON_IRANDARGAH_PAYMENT_REQUIRE_VER;

        /**
         * LP_Addon_IranDargah_Payment constructor.
         */
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Define Learnpress IranDargah payment constants.
         *
         */
        protected function _define_constants()
        {
            define('LP_ADDON_IRANDARGAH_PAYMENT_PATH', dirname(LP_ADDON_IRANDARGAH_PAYMENT_FILE));
            define('LP_ADDON_IRANDARGAH_PAYMENT_INC', LP_ADDON_IRANDARGAH_PAYMENT_PATH . '/inc/');
            define('LP_ADDON_IRANDARGAH_PAYMENT_URL', plugin_dir_url(LP_ADDON_IRANDARGAH_PAYMENT_FILE));
            define('LP_ADDON_IRANDARGAH_PAYMENT_TEMPLATE', LP_ADDON_IRANDARGAH_PAYMENT_PATH . '/templates/');
        }

        /**
         * Include required core files used in admin and on the frontend.
         *
         */
        protected function _includes()
        {
            include_once LP_ADDON_IRANDARGAH_PAYMENT_INC . 'class-lp-gateway-irandargah.php';
        }

        /**
         * Init hooks.
         */
        protected function _init_hooks()
        {
            // add payment gateway class
            add_filter('learn_press_payment_method', array($this, 'add_payment'));
            add_filter('learn-press/payment-methods', array($this, 'add_payment'));
        }

        /**
         * Enqueue assets.
         *
         */
        protected function _enqueue_assets()
        {
            return;

            if (LP()->settings->get('learn_press_irandargah.enable') == 'yes') {
                $user = learn_press_get_current_user();

                learn_press_assets()->enqueue_script('learn-press-irandargah-payment', $this->get_plugin_url('assets/js/script.js'), array());
                learn_press_assets()->enqueue_style('learn-press-irandargah', $this->get_plugin_url('assets/css/style.css'), array());

                $data = array(
                    'plugin_url' => plugins_url('', LP_ADDON_IRANDARGAH_PAYMENT_FILE),
                );
                wp_localize_script('learn-press-irandargah', 'learn_press_irandargah_info', $data);
            }
        }

        /**
         * Add IranDargah to payment system.
         *
         * @param $methods
         *
         * @return mixed
         */
        public function add_payment($methods)
        {
            $methods['irandargah'] = 'LP_Gateway_IranDargah';

            return $methods;
        }

        /**
         * Plugin links.
         *
         * @return array
         */
        public function plugin_links()
        {
            $links[] = '<a href="' . admin_url('admin.php?page=learn-press-settings&tab=payments&section=irandargah') . '">' . __('Settings', 'learnpress-irandargah') . '</a>';

            return $links;
        }
    }
}
