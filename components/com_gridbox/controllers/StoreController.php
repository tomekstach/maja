<?php
/**
 * @package   Gridbox
 * @author    Balbooa http://www.balbooa.com/
 * @copyright Copyright @ Balbooa
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Balbooa\Component\Gridbox\Site\Controller;

defined('_JEXEC') or die;

use Balbooa\Component\Gridbox\Site\Helper\GridboxHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Site\Model\ProfileModel;

class StoreController extends FormController
{
    /**
     * @return Balbooa\Component\Gridbox\Site\Model
     */
    public function getModel($name = 'Store', $prefix = 'Site', $config = ['ignore_request' => false])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function setCurrency()
    {
        $currency = $this->input->get('currency', '', 'string');
        $time = time() + 60 * 60 * 24 * 30;
        GridboxHelper::setcookie('gridbox-currency', $currency, $time);
        exit;
    }

    public function pendingPayments()
    {
        $model = $this->getModel();
        $model->pendingPayments();
        exit();
    }

    public function checkStripe()
    {
        $model = $this->getModel();
        $model->checkStripe();
        exit();
    }

    public function checkPaypal()
    {
        $model = $this->getModel();
        $model->checkPaypal();
        exit();
    }

    public function checkYandex()
    {
        $model = $this->getModel();
        $model->checkYandex();
        exit();
    }

    public function checkPayupl()
    {
        $model = $this->getModel();
        $model->checkPayupl();
        exit();
    }

    public function checkBarion()
    {
        $model = $this->getModel();
        $model->checkBarion();
        exit();
    }

    public function register()
    {
        $response = new \stdClass();
        // AstoSoft - start
        $email = $this->input->post->get('email1', '', 'string');
        $this->input->post->set('username', $email);
        // AstoSoft - end

        if (GridboxHelper::$store->checkout->registration) {
            /**
             * @var Joomla\CMS\Application\SiteApplication
             */
            $app = Factory::getApplication();
            $data = $this->input->post->getArray([]);
            $params = ComponentHelper::getParams('com_users');
            $data['groups'][] = $params->get('new_usertype', $params->get('guest_usergroup', 1));
            $data['email'] = PunycodeHelper::emailToPunycode($data['email1']);
            $data['password'] = $data['password1'];
            $user = new User();
            $response->status = $user->bind($data);
            if ($response->status) {
                $response->status = $user->save();
            }
            if ($response->status) {
                $credentials = [];
                $credentials['username'] = $data['username'];
                $credentials['password'] = $data['password1'];
                $options = [];
                $options['remember'] = false;
                $app->login($credentials, $options);
            } else {
                $response->message = $user->getError();
            }
        } else {
            $response->status = false;
        }
        $str = json_encode($response);
        echo $str;exit();
    }

    public function login()
    {
        /**
         * @var Joomla\CMS\Application\SiteApplication
         */
        $app = Factory::getApplication();
        $response = new \stdClass();
        $response->message = Text::_('LOGIN_ERROR');
        $options = [];
        $options['remember'] = $this->input->get('remember', 0, 'int') == 1;
        $credentials = [];
        $credentials['username'] = $this->input->get('username', '', 'username');
        $credentials['password'] = $this->input->get('password', '', 'raw');
        $response->status = $app->login($credentials, $options);
        if ($response->status && $options['remember']) {
            $app->setUserState('rememberLogin', true);
        }
        $str = json_encode($response);
        echo $str;exit();
    }

    public function saveUserProfile()
    {
        $id = Factory::getUser()->id;
        if (empty($id)) {
            exit;
        }
        $data = $this->input->post->getArray([]);
        $data['id'] = $id;
        if (isset($data['image'])) {
            $model = $this->getModel('account');
            $model->saveAthor($id, $data['image'], $data['description'], $data['title']);
            unset($data['image']);
            unset($data['description']);
            unset($data['title']);
        }

        $model = new ProfileModel();
        $model->save($data);
        exit;
    }

    public function logout()
    {
        /**
         * @var Joomla\CMS\Application\SiteApplication
         */
        $app = Factory::getApplication();
        $app->logout();
        header('Location: ' . Uri::root());
        exit();
    }

    public function getTaxCountries()
    {
        $countries = GridboxHelper::getTaxCountries();
        $str = json_encode($countries);
        print_r($str);exit;
    }

    public function getProductsList()
    {
        $id = $this->input->get('id', 0, 'int');
        $type = $this->input->get('type', '', 'string');
        $model = $this->getModel();
        $data = $model->getProductsList($id, $type);
        $str = json_encode($data);
        echo $str;exit;
    }

    public function getAppStoreFields()
    {
        $id = $this->input->get('id', 0, 'int');
        $type = $this->input->get('type', '', 'string');
        $model = $this->getModel();
        $data = $model->getAppStoreFields($id, $type);
        $str = json_encode($data);
        echo $str;exit;
    }

    public function getLiveSearchData()
    {
        $search = $this->input->get('search', '', 'string');
        $type = $this->input->get('type', '', 'string');
        $app_id = $this->input->get('app_id', '', 'string');
        $apps = $this->input->get('apps', [], 'array');
        $model = $this->getModel();
        $out = $model->getLiveSearchData($search, $type, $app_id, $apps);
        echo $out;exit;
    }

    public function setCartShipping()
    {
        $id = $this->input->get('id', 0, 'int');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $model->setCartShipping($id, $cart);
        exit;
    }

    public function setCartPayment()
    {
        $id = $this->input->get('id', 0, 'int');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $model->setCartPayment($id, $cart);
        exit;
    }

    public function setCustomerInfo()
    {
        $id = $this->input->get('id', 0, 'int');
        $value = $this->input->get('value', '', 'string');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $model->setCustomerInfo($id, $value, $cart);
        exit;
    }

    public function getWishlistAuthenticationMessage()
    {
        return (object) [
            'status' => false,
            'message' => Text::_('PLEASE_SIGN_IN_TO_MOVE_WISHLIST'),
        ];
    }

    public function addPostToWishlist()
    {
        $id = $this->input->get('id', 0, 'int');
        $wishlist = GridboxHelper::getWishlistId();
        if (GridboxHelper::$store->wishlist->login && Factory::getUser()->id == 0) {
            $obj = $this->getWishlistAuthenticationMessage();
        } else {
            $model = $this->getModel();
            $obj = $model->addPostToWishlist($id, $wishlist);
        }
        $str = json_encode($obj);
        echo $str;
        exit;
    }

    public function addPostToCart()
    {
        $id = $this->input->get('id', 0, 'int');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $obj = $model->addPostToCart($id, $cart);
        $str = json_encode($obj);
        echo $str;
        exit;
    }

    public function deleteStoreBadge()
    {
        GridboxHelper::checkUserEditLevel();
        $id = $this->input->get('id', 0, 'int');
        $model = $this->getModel();
        $model->deleteStoreBadge($id);
        echo "{}";exit;
    }

    public function updateStoreBadge()
    {
        GridboxHelper::checkUserEditLevel();
        $badge = new \stdClass();
        $badge->id = $this->input->get('id', 0, 'int');
        $badge->title = $this->input->get('title', '', 'string');
        $badge->color = $this->input->get('color', '#1da6f4', 'string');
        $model = $this->getModel();
        $model->updateStoreBadge($badge);
        echo "{}";exit;
    }

    public function getStoreBadge()
    {
        $model = $this->getModel();
        $badges = $model->getStoreBadge();
        $str = json_encode($badges);
        echo $str;
        exit;
    }

    public function addProductBadge()
    {
        $model = $this->getModel();
        $obj = $model->addProductBadge();
        $str = json_encode($obj);
        echo $str;
        exit;
    }

    public function pagseguroCallback()
    {
        $transactionCode = $this->input->get("transactionCode", '', 'string');
        $id = $this->input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->pagseguroCallback($id, $transactionCode);
        echo Uri::root() . "index.php?option=com_gridbox&task=store.setOrder&time=" . time();
        exit;
    }

    public function robokassaCallback()
    {
        $inv_id = $this->input->get("InvId", 0, 'int');
        $model = $this->getModel();
        $model->robokassaCallback($inv_id);
        exit;
    }

    public function liqpayCallback()
    {
        $data = $this->input->get('data', '', 'string');
        $signature = $this->input->get('signature', '', 'string');
        $model = $this->getModel();
        $model->liqpayCallback($data, $signature);
        exit;
    }

    public function dotpayCallback()
    {
        $data = $this->input->post->getArray([]);
        $model = $this->getModel();
        $model->dotpayCallback($data);
        echo "OK";
        exit;
    }

    public function monoCallback()
    {
        $data = $this->input->post->getArray([]);
        $model = $this->getModel();
        $model->monoCallback($data);
        exit;
    }

    public function yookassaCallback()
    {
        header('HTTP/1.0 200 OK');
        flush();
        $model = $this->getModel();
        $model->checkYandex();
        exit;
    }

    public function barionCallback()
    {
        header('HTTP/1.0 200 OK');
        flush();
        $paymentId = $this->input->get('paymentId', '', 'string');
        $model = $this->getModel();
        $model->barionCallback($paymentId);
        exit;
    }

    public function payfastCallback()
    {
        header('HTTP/1.0 200 OK');
        flush();
        $data = $this->input->post->getArray([]);
        $model = $this->getModel();
        $model->payfastCallback($data);
        exit;
    }

    public function klarnaCallback()
    {
        $input = Factory::getApplication()->input;
        $order_id = $input->get('order_id', '', 'string');
        $model = $this->getModel();
        $model->klarnaCallback($order_id);
        exit;
    }

    public function submitLiqpay()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitLiqpay($id);
    }

    public function submitBarion()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitBarion($id);
    }

    public function submitSquare()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitSquare($id);
    }

    public function submitDotpay()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitDotpay($id);
    }

    public function submitPayfast()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitPayfast($id);
    }

    public function mollieCallback()
    {
        $id = $this->input->get('id', '', 'string');
        $model = $this->getModel();
        $model->mollieCallback($id);
        exit;
    }

    public function submitMono()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitMono($id);
    }

    public function submitMollie()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitMollie($id);
    }

    public function submitRobokassa()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitRobokassa($id);
    }

    public function submitPayupl()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitPayupl($id);
    }

    public function submitPagseguro()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitPagseguro($id);
    }

    public function submitKlarna()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitKlarna($id);
    }

    public function submitYandexKassa()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submitYandexKassa($id);
    }

    public function submit2checkout()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $model = $this->getModel();
        $model->submit2checkout($id);
    }

    public function stripeCharges()
    {
        $input = Factory::getApplication()->input;
        $id = $input->cookie->get('gridbox_store_order', 0, 'int');
        $payment_id = $input->get('id', 0, 'int');
        $model = $this->getModel();
        $model->stripeCharges($id, $payment_id);
    }

    public function updateOrder()
    {
        $id = $this->input->cookie->get('gridbox_store_order', 0, 'int');
        $params = $this->input->get('params', '{}', 'string');
        $model = $this->getModel();
        $model->updateOrder($id, $params);
        exit;
    }

    public function payAuthorize()
    {
        $id = $this->input->cookie->get('gridbox_store_order', 0, 'int');
        $cardNumber = $this->input->get('cardNumber', '', 'string');
        $expirationDate = $this->input->get('expirationDate', '', 'string');
        $cardCode = $this->input->get('cardCode', '', 'string');
        $cardNumber = str_replace(' ', '', $cardNumber);
        $expArray = explode('/', $expirationDate);
        $expirationDate = $expArray[1] . '-' . $expArray[0];
        $model = $this->getModel();
        $model->payAuthorize($id, $cardNumber, $expirationDate, $cardCode);
    }

    public function createOrder()
    {
        if (GridboxHelper::$store->checkout->login
            && !GridboxHelper::$store->checkout->guest
            && empty(Factory::getUser()->id)) {
            $obj = new \stdClass();
            $obj->denied = true;
            $str = json_encode($obj);
            print_r($str);exit;
        }
        $input = Factory::getApplication()->input;
        $post = $input->post->getArray([]);
        $id = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $order = $model->createOrder($post, $id);
        $order->url = Uri::root() . "index.php?option=com_gridbox&task=store.setOrder&time=" . time();
        $str = json_encode($order);
        print_r($str);exit;
    }

    public function setOrder()
    {
        GridboxHelper::setOrder();
    }

    public function getPaymentOptions()
    {
        $input = Factory::getApplication()->input;
        $payment = $input->get('payment', 0, 'int');
        $model = $this->getModel();
        $obj = $model->getPaymentOptions($payment);
        $str = json_encode($obj);
        print_r($str);exit;
    }

    public function removeProductFromCart()
    {
        $input = Factory::getApplication()->input;
        $product_id = $input->get('id', 0, 'int');
        $cart_id = $input->cookie->get('gridbox_store_cart', 0, 'int');
        GridboxHelper::removeProductFromCart($product_id, $cart_id);
        exit;
    }

    public function applyPromoCode()
    {
        $input = Factory::getApplication()->input;
        $model = $this->getModel();
        $promo = $input->get('promo', '', 'string');
        $id = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $out = $model->applyPromoCode($promo, $id);
        echo $out;exit;
    }

    public function getStoreCart()
    {
        $input = Factory::getApplication()->input;
        $view = $input->get('view', '', 'string');
        $id = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $out = $model->getStoreCartHTML($view, $id);
        echo $out;exit;
    }

    public function clearWishlist()
    {
        $id = GridboxHelper::getWishlistId();
        $model = $this->getModel();
        $model->clearWishlist($id);
        $this->getWishlist();
    }

    public function removeProductFromWishlist()
    {
        $id = GridboxHelper::getWishlistId();
        $product_id = $this->input->get('id', 0, 'int');
        $model = $this->getModel();
        $model->clearWishlist($id, $product_id);
        $this->getWishlist();
    }

    public function removeExtraOptionCart()
    {
        $id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $product_id = $this->input->get('id', 0, 'int');
        $key = $this->input->get('key', '', 'string');
        $field_id = $this->input->get('field_id', 0, 'int');
        $model = $this->getModel();
        $model->removeExtraOptionCart($id, $product_id, $key, $field_id);
        exit;
    }

    public function removeExtraOptionWishlist()
    {
        $id = GridboxHelper::getWishlistId();
        $product_id = $this->input->get('id', 0, 'int');
        $key = $this->input->get('key', '', 'string');
        $field_id = $this->input->get('field_id', 0, 'int');
        $model = $this->getModel();
        $model->removeExtraOptionWishlist($id, $product_id, $key, $field_id);
        exit;
    }

    public function moveProductFromWishlist()
    {
        $id = GridboxHelper::getWishlistId();
        $product_id = $this->input->get('id', 0, 'int');
        $cart_id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $model->moveProductFromWishlist($id, $product_id, $cart_id);
        $this->getWishlist();
    }

    public function moveProductsFromWishlist()
    {
        $id = GridboxHelper::getWishlistId();
        $cart_id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $products = GridboxHelper::getStoreWishlistProducts($id);
        $model = $this->getModel();
        foreach ($products as $key => $product) {
            $model->moveProductFromWishlist($id, $product->id, $cart_id);
        }
        $this->getWishlist();
    }

    public function getWishlist()
    {
        $view = $this->input->get('view', '', 'string');
        $id = GridboxHelper::getWishlistId();
        $model = $this->getModel();
        $out = $model->getWishlistHTML($view, $id);
        echo $out;exit;
    }

    public function addProductToWishlist()
    {
        $id = $this->input->get('id', 0, 'int');
        $variation = $this->input->get('variation', '', 'string');
        $wishlist = GridboxHelper::getWishlistId();
        $extra_options = $this->input->get('extra_options', '', 'string');
        $cart_id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $booking = $this->input->get('booking', '{}', 'string');
        if (GridboxHelper::$store->wishlist->login && Factory::getUser()->id == 0) {
            $obj = $this->getWishlistAuthenticationMessage();
        } else {
            $model = $this->getModel();
            $attachments = $model->getAttachments($id, $cart_id);
            $obj = $model->addProductToWishlist($id,
                $wishlist,
                $variation,
                $extra_options,
                $attachments,
                $booking);
        }
        $str = json_encode($obj);
        echo $str;
        exit;
    }

    public function upgradePlan()
    {
        $id = $this->input->get('id', 0, 'int');
        $upgrade_id = $this->input->get('upgrade_id', 0, 'int');
        $model = $this->getModel();
        $flag = $model->upgradePlan($id, $upgrade_id);
        if (!$flag) {
            $this->addProductToCart();
        } else {
            echo "upgraded";
        }
        exit;
    }

    public function addProductToCart()
    {
        $id = $this->input->get('id', 0, 'int');
        $variation = $this->input->get('variation', '', 'string');
        $quantity = $this->input->get('quantity', 0, 'int');
        $cart = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $extra_options = $this->input->get('extra_options', '{}', 'string');
        $booking = $this->input->get('booking', '{}', 'string');
        $renew_id = $this->input->get('renew_id', 0, 'int');
        $upgrade_id = $this->input->get('upgrade_id', 0, 'int');
        $plan_key = $this->input->get('plan_key', '', 'string');
        $model = $this->getModel();
        $attachments = $model->getAttachments($id, $cart);
        $model->addProductToCart($id,
            $cart,
            $quantity,
            $variation,
            $extra_options,
            $renew_id,
            $plan_key,
            $upgrade_id,
            $attachments,
            $booking);
        exit;
    }

    public function removeAttachment()
    {
        $id = $this->input->post->get('id', 0, 'int');
        $is_admin = $this->input->post->get('is_admin', 0, 'int');
        $cart = $is_admin == 1 ? 0 : $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        GridboxHelper::$storeHelper->removeAttachment($id, $cart);
        exit;
    }

    public function uploadAttachmentFile()
    {
        $file = $_FILES['file'];
        $id = $this->input->post->get('id', 0, 'int');
        $option_id = $this->input->post->get('option_id', 0, 'int');
        $is_admin = $this->input->post->get('is_admin', 0, 'int');
        $cart = $is_admin == 1 ? 0 : $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $obj = $model->uploadAttachmentFile($id, $cart, $file, $option_id);
        $str = json_encode($obj);
        echo $str;
        exit();
    }

    public function setCartCountry()
    {
        $id = $this->input->cookie->get('gridbox_store_cart', 0, 'int');
        $country = $this->input->get('country', '', 'string');
        $region = $this->input->get('region', '', 'string');
        $model = $this->getModel();
        $model->setCartCountry($id, $country, $region);
        exit;
    }

    public function updateProductQuantity()
    {
        $input = Factory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $quantity = $input->get('quantity', 0, 'int');
        $cart = $input->cookie->get('gridbox_store_cart', 0, 'int');
        $model = $this->getModel();
        $model->updateProductQuantity($id, $cart, $quantity);
        exit;
    }

    public function uploadDigitalFile()
    {
        $file = isset($_FILES['file']) ? $_FILES['file'] : [];
        $id = $this->input->post->get('id', 0, 'int');
        $model = $this->getModel();
        $obj = $model->uploadDigitalFile($file, $id);
        $str = json_encode($obj);
        echo $str;
        exit();
    }

    public function downloadDigitalFile()
    {
        $token = $this->input->get('file', '', 'string');
        $model = $this->getModel();
        $model->downloadDigitalFile($token);
    }

    public function downloadSubscriptionFile()
    {
        $file = $this->input->get('file', '', 'string');
        $file = base64_decode($file);
        $array = explode('+', $file);
        $s_id = $array[0];
        $p_id = $array[1];
        $model = $this->getModel();
        $model->downloadSubscriptionFile($s_id, $p_id);
    }

    public function sendReminder()
    {
        GridboxHelper::$storeHelper->sendReminder();
        exit;
    }

    public function sendDelayEmails()
    {
        GridboxHelper::$storeHelper->sendDelayEmail();
        exit;
    }

    public function sendAppointmentReminder()
    {
        GridboxHelper::$storeHelper->sendAppointmentReminder();
        exit;
    }
}
