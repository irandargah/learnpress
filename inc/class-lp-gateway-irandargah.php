<?php
/**
 * IranDargah payment gateway class.
 *
 * @author   IranDargah.com
 * @package  LearnPress/IranDargah/Classes
 * @version  1.0.0
 */

// Prevent loading this file directly
defined('ABSPATH') || exit;

if (!class_exists('LP_Gateway_IranDargah')) {
    /**
     * Class LP_Gateway_IranDargah
     */
    class LP_Gateway_IranDargah extends LP_Gateway_Abstract
    {

        /**
         * @var array
         */
        private $form_data = array();

        /**
         * @var string
         */
        private $wsdl = 'https://www.dargaah.com/wsdl';

        /**
         * @var string
         */
        private $startPay = 'https://www.dargaah.com/ird/startpay/';

        /**
         * @var string
         */
        private $verifyUrl = 'https://www.dargaah.com/wsdl';

        /**
         * @var string
         */
        private $soap = true;

        /**
         * @var string
         */
        private $merchantID = null;

        /**
         * @var array|null
         */
        protected $settings = null;

        /**
         * @var null
         */
        protected $order = null;

        /**
         * @var null
         */
        protected $posted = null;

        /**
         *
         * @var string
         */
        protected $authority = null;

        /**
         * LP_Gateway_IranDargah constructor.
         */
        public function __construct()
        {
            $this->id = 'irandargah';

            $this->method_title = __('IranDargah', 'learnpress-irandargah');
            $this->method_description = __('Make a payment with IranDargah.', 'learnpress-irandargah');
            $this->icon = '';

            // Get settings
            $this->title = LP()->settings->get("{$this->id}.title", $this->method_title);
            $this->description = LP()->settings->get("{$this->id}.description", $this->method_description);

            $settings = LP()->settings;

            // Add default values for fresh installs
            if ($settings->get("{$this->id}.enable")) {
                $this->settings = array();
                $this->settings['merchant_id'] = $settings->get("{$this->id}.merchant_id");
            }

            $this->merchantID = $this->settings['merchant_id'];

            if (did_action('learn_press/irandargah-add-on/loaded')) {
                return;
            }

            // check payment gateway enable
            add_filter('learn-press/payment-gateway/' . $this->id . '/available', array(
                $this,
                'irandargah_available',
            ), 10, 2);

            do_action('learn_press/irandargah-add-on/loaded');

            parent::__construct();

            // web hook
            if (did_action('init')) {
                $this->register_web_hook();
            } else {
                add_action('init', array($this, 'register_web_hook'));
            }
            add_action('learn_press_web_hooks_processed', array($this, 'web_hook_process_irandargah'));

            add_action("learn-press/before-checkout-order-review", array($this, 'error_message'));
        }

        /**
         * Register web hook.
         *
         * @return array
         */
        public function register_web_hook()
        {
            learn_press_register_web_hook('irandargah', 'learn_press_irandargah');
        }

        /**
         * Admin payment settings.
         *
         * @return array
         */
        public function get_settings()
        {

            return apply_filters('learn-press/gateway-payment/irandargah/settings',
                array(
                    array(
                        'title' => __('Enable', 'learnpress-irandargah'),
                        'id' => '[enable]',
                        'default' => 'no',
                        'type' => 'yes-no',
                    ),
                    array(
                        'type' => 'text',
                        'title' => __('Title', 'learnpress-irandargah'),
                        'default' => __('IranDargah', 'learnpress-irandargah'),
                        'id' => '[title]',
                        'class' => 'regular-text',
                        'visibility' => array(
                            'state' => 'show',
                            'conditional' => array(
                                array(
                                    'field' => '[enable]',
                                    'compare' => '=',
                                    'value' => 'yes',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'textarea',
                        'title' => __('Description', 'learnpress-irandargah'),
                        'default' => __('Pay with IranDargah', 'learnpress-irandargah'),
                        'id' => '[description]',
                        'editor' => array(
                            'textarea_rows' => 5,
                        ),
                        'css' => 'height: 100px;',
                        'visibility' => array(
                            'state' => 'show',
                            'conditional' => array(
                                array(
                                    'field' => '[enable]',
                                    'compare' => '=',
                                    'value' => 'yes',
                                ),
                            ),
                        ),
                    ),
                    array(
                        'title' => __('Merchant ID', 'learnpress-irandargah'),
                        'id' => '[merchant_id]',
                        'type' => 'text',
                        'visibility' => array(
                            'state' => 'show',
                            'conditional' => array(
                                array(
                                    'field' => '[enable]',
                                    'compare' => '=',
                                    'value' => 'yes',
                                ),
                            ),
                        ),
                    ),
                )
            );
        }

        /**
         * Payment form.
         */
        public function get_payment_form()
        {
            ob_start();
            $template = learn_press_locate_template('form.php', learn_press_template_path() . '/addons/irandargah-payment/', LP_ADDON_IRANDARGAH_PAYMENT_TEMPLATE);
            include $template;

            return ob_get_clean();
        }

        /**
         * Error message.
         *
         * @return array
         */
        public function error_message()
        {
            if (!isset($_SESSION)) {
                session_start();
            }

            if (isset($_SESSION['irandargah_error']) && intval($_SESSION['irandargah_error']) === 1) {
                $_SESSION['irandargah_error'] = 0;
                $template = learn_press_locate_template('payment-error.php', learn_press_template_path() . '/addons/irandargah-payment/', LP_ADDON_IRANDARGAH_PAYMENT_TEMPLATE);
                include $template;
            }
        }

        /**
         * @return mixed
         */
        public function get_icon()
        {
            if (empty($this->icon)) {
                $this->icon = LP_ADDON_IRANDARGAH_PAYMENT_URL . 'assets/images/irandargah.png';
            }

            return parent::get_icon();
        }

        /**
         * Check gateway available.
         *
         * @return bool
         */
        public function irandargah_available()
        {

            if (LP()->settings->get("{$this->id}.enable") != 'yes') {
                return false;
            }

            return true;
        }

        /**
         * Get form data.
         *
         * @return array
         */
        public function get_form_data()
        {
            if ($this->order) {
                $user = learn_press_get_current_user();
                $currency_code = learn_press_get_currency();
                if ($currency_code == 'IRR') {
                    $amount = $this->order->order_total;
                } else {
                    $amount = $this->order->order_total * 10;
                }

                $this->form_data = array(
                    'amount' => $amount,
                    'currency' => strtolower(learn_press_get_currency()),
                    'token' => $this->token,
                    'description' => sprintf(__("Charge for %s", "learnpress-irandargah"), $user->get_data('email')),
                    'customer' => array(
                        'name' => $user->get_data('display_name'),
                        'billing_email' => $user->get_data('email'),
                    ),
                    'errors' => isset($this->posted['form_errors']) ? $this->posted['form_errors'] : '',
                );
            }

            return $this->form_data;
        }

        /**
         * Validate form fields.
         *
         * @return bool
         * @throws Exception
         * @throws string
         */
        public function validate_fields()
        {
            $posted = learn_press_get_request('learn-press-irandargah');
            $email = !empty($posted['email']) ? $posted['email'] : "";
            $mobile = !empty($posted['mobile']) ? $posted['mobile'] : "";
            $error_message = array();
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_message[] = __('Invalid email format.', 'learnpress-irandargah');
            }
            if (!empty($mobile) && !preg_match("/^(09)(\d{9})$/", $mobile)) {
                $error_message[] = __('Invalid mobile format.', 'learnpress-irandargah');
            }

            if ($error = sizeof($error_message)) {
                throw new Exception(sprintf('<div>%s</div>', join('</div><div>', $error_message)), 8000);
            }
            $this->posted = $posted;

            return $error ? false : true;
        }

        /**
         * IranDargah payment process.
         *
         * @param $order
         *
         * @return array
         * @throws string
         */
        public function process_payment($order)
        {
            $this->order = learn_press_get_order($order);
            $irandargah = $this->get_irandargah_authority();
            $gateway_url = $this->startPay . $this->authority;

            $json = array(
                'result' => $irandargah ? 'success' : 'fail',
                'redirect' => $irandargah ? $gateway_url : '',
            );

            return $json;
        }

        /**
         * Get IranDargah Authority.
         *
         * @return bool|object
         * @throws string
         */
        public function get_irandargah_authority()
        {
            if ($this->get_form_data()) {
                $checkout = LP()->checkout();
                $data = [
                    'merchantID' => $this->merchantID,
                    'amount' => $this->form_data['amount'],
                    'description' => $this->form_data['description'],
                    'mobile' => (!empty($this->posted['mobile'])) ? $this->posted['mobile'] : "",
                    'callbackURL' => get_site_url() . '/?' . learn_press_get_web_hook('irandargah') . '=1&order_id=' . $this->order->get_id(),
                ];

                $client = new SoapClient($this->wsdl, ['encoding' => 'UTF-8']);
                $result = $client->IRDPayment($data);

                if ($result->status == 200) {
                    $this->authority = $result->authority;
                    return true;
                }
            }
            return false;
        }

        /**
         * Handle a web hook
         *
         */
        public function web_hook_process_irandargah()
        {
            $request = $_REQUEST;
            if (isset($request['learn_press_irandargah']) && intval($request['learn_press_irandargah']) === 1) {
                if ($request['code'] == '100') {
                    $order = LP_Order::instance($request['order_id']);
                    $currency_code = learn_press_get_currency();
                    if ($currency_code == 'IRR') {
                        $amount = $order->order_total;
                    } else {
                        $amount = $order->order_total * 10;
                    }

                    $data = array(
                        'merchantID' => $this->merchantID,
                        'authority' => $_POST['authority'],
                        'amount' => $amount,
                    );
                    $client = new SoapClient($this->verifyUrl, ['encoding' => 'UTF-8']);
                    $result = $client->IRDVerification($data);
                    if ($result->status == 100) {
                        $request["refID"] = $result->refID;
                        $this->authority = intval($_POST['authority']);
                        $this->payment_status_completed($order, $request);
                        wp_redirect(esc_url($this->get_return_url($order)));
                        exit();
                    }
                }

                if (!isset($_SESSION)) {
                    session_start();
                }

                $_SESSION['irandargah_error'] = 1;

                wp_redirect(esc_url(learn_press_get_page_link('checkout')));
                exit();
            }
        }

        /**
         * Handle a completed payment
         *
         * @param LP_Order
         * @param request
         */
        protected function payment_status_completed($order, $request)
        {

            // order status is already completed
            if ($order->has_status('completed')) {
                exit;
            }

            $this->payment_complete($order, (!empty($request['refID']) ? $request['refID'] : ''), __('Payment has been successfully completed', 'learnpress-irandargah'));
            update_post_meta($order->get_id(), '_irandargah_RefID', $request['refID']);
            update_post_meta($order->get_id(), '_irandargah_authority', $request['authority']);
        }

        /**
         * Handle a pending payment
         *
         * @param  LP_Order
         * @param  request
         */
        protected function payment_status_pending($order, $request)
        {
            $this->payment_status_completed($order, $request);
        }

        /**
         * @param        LP_Order
         * @param string $txn_id
         * @param string $note - not use
         */
        public function payment_complete($order, $trans_id = '', $note = '')
        {
            $order->payment_complete($trans_id);
        }
    }
}
