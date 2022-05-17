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
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\FormHelper;

require_once __DIR__ . '/chargily/vendor/autoload.php';


class Epaydz extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'epaydz';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'AOUAM Mohamed';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();
        $this->displayName = $this->l('Epay dz');
        $this->description = $this->l('this module will allow your customers to pay using ELDAHABIA and CIB cards .');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }
    public function install()
    {
        return parent::install() &&
            $this->registerHook('paymentOptions') &&
            $this->dbInit();
    }
    public function uninstall()
    {
        return parent::uninstall();
    }
    public function dbInit()
    {
        if (Configuration::get('EPAYDZ_STATE_1') == null) {
            $id1 = $this->addOrderState("Awaiting online payment", "#0000FF", 0);

            if ($id1 != null)
                Configuration::updateValue('EPAYDZ_STATE_1', $id1);
        }
        if (Configuration::get('EPAYDZ_STATE_2')) {

            $id2 = $this->addOrderState("Paid by card", "#00FF00", 1);
            if ($id2 != null)
                Configuration::updateValue('EPAYDZ_STATE_2', $id2);
        }
        return true;
    }
    public function addOrderState($name, $color, $paid)
    {
        $state_exist = false;
        $states = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($states as $state) {
            if (in_array($name, $state)) {
                return null;
            }
        }

        // If the state does not exist, we create it.
        if (!$state_exist) {
            // create new order state
            $order_state = new OrderState();
            $order_state->color = $color;
            $order_state->send_email = false;
            $order_state->module_name = $this->name;
            $order_state->name = array();
            $order_state->paid = $paid;
            $languages = Language::getLanguages(false);
            foreach ($languages as $language)
                $order_state->name[$language['id_lang']] = $name;
            $order_state->add();
        }

        return $order_state->id;
    }
    public function hookPaymentOptions()
    {
        $edahabia = new PaymentOption();
        $edahabia->setCallToActionText($this->displayName)->setAction($this->context->link->getModuleLink($this->name, 'begin'));
        $edahabia->setAdditionalInformation("<p>Use your edahabia or CIB card to pay your order.</p>");
        $edahabia->setForm($this->getEdahabiaForm());
        return [$edahabia];
    }
    public function getEdahabiaForm()
    {
        $this->context->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'begin', array(), true),
        ]);
        return $this->context->smarty->fetch('module:epaydz/views/templates/front/payment_form.tpl');
    }
    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user
            $key = (string) Tools::getValue('EPAYDZ_KEY');
            $secret = (string) Tools::getValue('EPAYDZ_SECRET');
            $os1 = (string) Tools::getValue('EPAYDZ_STATE_1');
            $os2 = (string) Tools::getValue('EPAYDZ_STATE_2');
            $desc = (string) Tools::getValue('EPAYDZ_DESC');
    
            // check that the value is valid
            if (empty($key) || empty($secret) || empty($os1) || empty($os2) ) {
                // invalid value, show an error
                $output = $this->displayError($this->l('Invalid Configuration value'));
            } else {
                // value is ok, update it and display a confirmation message
                Configuration::updateValue('EPAYDZ_KEY', $key);
                Configuration::updateValue('EPAYDZ_SECRET', $secret);
                Configuration::updateValue('EPAYDZ_STATE_1', $os1);
                Configuration::updateValue('EPAYDZ_STATE_2', $os2);
                Configuration::updateValue('EPAYDZ_DESC', $desc);
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }
    public function displayForm()
    {
        // Init Fields form array
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Chargily key'),
                        'name' => 'EPAYDZ_KEY',
                        'required' => true,
                        'desc' => $this->l('Chargily API key'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Chargily secret'),
                        'name' => 'EPAYDZ_SECRET',
                        'required' => true,
                        'desc' => $this->l('Chargily secret key'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('ID Order status 1'),
                        'name' => 'EPAYDZ_STATE_1',
                        'required' => true,
                        'desc' => $this->l('Order status before payment'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('ID Order status 2'),
                        'name' => 'EPAYDZ_STATE_2',
                        'required' => true,
                        'desc' => $this->l('Order status after payment'),
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Chargily payment description'),
                        'name' => 'EPAYDZ_DESC',
                        'required' => false,
                        'desc' => $this->l('Description to be shown to the customer once in chargily payment page'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // Load current value into the form
        $helper->fields_value['EPAYDZ_KEY'] = Tools::getValue('EPAYDZ_KEY', Configuration::get('EPAYDZ_KEY'));
        $helper->fields_value['EPAYDZ_SECRET'] = Tools::getValue('EPAYDZ_SECRET', Configuration::get('EPAYDZ_SECRET'));
        $helper->fields_value['EPAYDZ_STATE_1'] = Tools::getValue('EPAYDZ_STATE_1', Configuration::get('EPAYDZ_STATE_1'));
        $helper->fields_value['EPAYDZ_STATE_2'] = Tools::getValue('EPAYDZ_STATE_2', Configuration::get('EPAYDZ_STATE_2'));
        $helper->fields_value['EPAYDZ_DESC'] = Tools::getValue('EPAYDZ_DESC', Configuration::get('EPAYDZ_DESC'));

        return $helper->generateForm([$form]);
    }
}
