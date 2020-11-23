<?php
/**
 * Template for displaying IranDargah payment form.
 *
 * This template can be overridden by copying it to yourtheme/learnpress/addons/irandargah-payment/form.php.
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

<p><?php echo $this->get_description(); ?></p>

<div id="learn-press-irandargah-form" class="<?php if (is_rtl()) {
    echo ' learn-press-form-irandargah-rtl';
}
?>">
    <p class="learn-press-form-row">
        <label><?php echo wp_kses(__('Email', 'learnpress-irandargah'), array('span' => array())); ?></label>
        <input type="text" name="learn-press-irandargah[email]" id="learn-press-irandargah-payment-email"
               maxlength="19" value=""  placeholder="info@midiyasoft.com"/>
		<div class="learn-press-irandargah-form-clear"></div>
    </p>
	<div class="learn-press-irandargah-form-clear"></div>
    <p class="learn-press-form-row">
        <label><?php echo wp_kses(__('Mobile', 'learnpress-irandargah'), array('span' => array())); ?></label>
        <input type="text" name="learn-press-irandargah[mobile]" id="learn-press-irandargah-payment-mobile" value=""
               placeholder="09121234567"/>
		<div class="learn-press-irandargah-form-clear"></div>
    </p>
	<div class="learn-press-irandargah-form-clear"></div>
</div>
