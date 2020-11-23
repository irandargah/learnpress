<?php
/**
 * Template for displaying IranDargah payment error message.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/irandargah-payment/payment-error.php.
 *
 * @author   IranDargah.com
 * @package  LearnPress/IranDargah/Templates
 * @version  1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined('ABSPATH') || exit();
?>

<?php $settings = LP()->settings;?>

<div class="learn-press-message error ">
	<div><?php echo __('Transation failed', 'learnpress-irandargah'); ?></div>
</div>
