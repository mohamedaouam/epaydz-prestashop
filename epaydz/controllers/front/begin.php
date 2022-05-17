<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    Mohamed AOUAM <mohamed.aouam@outlook.com>
 * @copyright Since 2022
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
use Chargily\ePay\Chargily;

class EpaydzBeginModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        parent::postProcess();
        $key = Configuration::get("EPAYDZ_KEY");
        $secret = Configuration::get('EPAYDZ_SECRET');
        $card = Tools::getValue('cemail');
        $c = $this->context->cart;
        $this->context->smarty->assign('order', $c);
        $customer = new Customer($this->context->cart->id_customer);
        $customer = new Customer($c->id_customer);
        // if (!Validate::isLoadedObject($customer))
        //     Tools::redirect('index.php?controller=order&step=1');

        $currency = $this->context->currency;
        $total = (float)$c->getOrderTotal(true, Cart::BOTH);
        $mailVars = array(
             '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
             '{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
             '{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
         );
         $f = $this->module->validateOrder($c->id, Configuration::get('EPAYDZ_STATE_1'), $c->getOrderTotal(), $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
         
        $config = [
            //credentials
            'api_key' => $key,
            'api_secret' => $secret,
            //urls
            'urls' => [
                'back_url' => $this->context->link->getPageLink('order-confirmation').'?id_cart='.$c->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key, // this is where client redirected after payment processing
                'webhook_url' => $this->context->link->getModuleLink($this->module->name, 'validation', ["is_cart" => $c->id]), // this is where you receive payment informations
            ],
            'mode' => Tools::getValue('mode'),
            'payment' => [
                'number' => $c->id, 
                'client_name' => $customer->firstname .' '. $customer->lastname, // Client name
                'client_email' => $customer->email , // This is where client receive payment receipt after confirmation
                'amount' => $c->getOrderTotal(),  
                'discount' => 0, 
                'description' => Configuration::get('EPAYDZ_DESC'),

            ]
        ];
        $chargily = new Chargily($config);
        $rurl = $chargily->getRedirectUrl();
        if($rurl){
            header('location: ' .$rurl);
        }
        $this->setTemplate('module:epaydz/views/templates/front/redirection.tpl');
    }
    public function init()
    {
        parent::init();
    }
    public function setMedia()
    {
        parent::setMedia();
    }
}
