<?php
/**
 * @package   Gridbox
 * @author    Balbooa http://www.balbooa.com/
 * @copyright Copyright @ Balbooa
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

ob_start();
$style = $view == 'gridbox' || self::$editItem->options->login ? 'style="display: none;"' : '';
?>
<div class="ba-create-account-wrapper" data-wrapper="create-account" <?php echo $style; ?>>
    <span class="ba-login-headline"><?php echo Text::_('CREATE_AN_ACCOUNT'); ?></span>
<?php
if ($view == 'gridbox' || self::$editItem->facebook->enable || self::$editItem->google->enable) {
    include 'integrations.php';
}
// AstoSoft - start
?>
    <div class="ba-login-fields-wrapper">
        <div class="ba-login-field-wrapper">
            <span class="ba-login-field-label"><?php echo Text::_('NAME'); ?></span>
            <input class="ba-login-field" type="text" name="name">
        </div>
        <div class="ba-login-field-wrapper">
            <span class="ba-login-field-label"><?php echo JText::_('EMAIL'); ?></span>
            <input class="ba-login-field" type="email" name="email1">
        </div>
        <div class="ba-login-field-wrapper">
            <span class="ba-login-field-label"><?php echo Text::_('PASSWORD'); ?></span>
            <input class="ba-login-field" type="password" name="password1" autocomplete="new-password">
        </div>
        <div class="ba-login-field-wrapper">
            <span class="ba-login-field-label"><?php echo Text::_('CONFIRM_PASSWORD'); ?></span>
            <input class="ba-login-field" type="password" name="password2" autocomplete="new-password">
        </div>
<?php
// AstoSoft - end
if ($view == 'gridbox' || self::$editItem->acceptance->enable) {
    ?>
        <div class="ba-login-field-wrapper ba-login-acceptance-wrapper">
            <div class="ba-checkbox-wrapper">
                <div class="ba-login-acceptance"><?php echo self::$editItem->acceptance->html; ?></div>
                <label class="ba-checkbox">
                    <input type="checkbox" name="acceptance">
                    <span></span>
                </label>
            </div>
        </div>
<?php
}
?>
    </div>
<?php
if ($view != 'gridbox' && !empty(self::$editItem->options->recaptcha)) {
    ?>
    <div class="ba-login-captcha-wrapper" data-type="<?php echo self::$editItem->options->recaptcha; ?>">

    </div>
<?php
}
?>
    <div class="ba-login-btn-wrapper">
        <span class="ba-login-btn" data-action="registration"><?php echo Text::_('CREATE_AN_ACCOUNT'); ?></span>
    </div>
<?php
if ($view == 'gridbox' || self::$editItem->options->login) {
    ?>
    <div class="ba-login-footer-wrapper">
        <span class="ba-login-field-label" data-step="login"><?php echo Text::_('BACK_TO_LOGIN'); ?></span>
    </div>
<?php
}
?>
</div>
<?php
$out = ob_get_contents();
ob_end_clean();