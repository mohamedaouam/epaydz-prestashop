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

class EpaydzValidationModuleFrontController extends ModuleFrontController {
    public function postProcess()
    {
        parent::postProcess();
        $key = Configuration::get('EPAYDZ_KEY');
        $secret = Configuration::get('EPAYDZ_SECRET');
        $os = (int)Configuration::get('EPAYDZ_STATE_2');
        $chargily = new Chargily([
            //credentials
            'api_key' => $key,
            'api_secret' => $secret,
        ]);
        $response = null;
        if ($chargily->checkResponse()) {
            $response = $chargily->getResponseDetails();
        }
        if($response != null && $response['invoice']['invoice_number'] == Tools::getValue('id_cart')){
            $order = new Order($response['invoice']['invoice_number']);
            $order->setCurrentState($os);
        }
        $this->setTemplate('module:epaydz/views/templates/front/e.tpl');
    }
}