{**
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
 *}
<form action="{$action}" id="payment-form" method="post">
<style>
/* The container */
.epaydz-container {
  display: block;
  position: relative;
  padding-left: 35px;
  margin-bottom: 12px;
  cursor: pointer;
  font-size: 22px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Hide the browser's default radio button */
.epaydz-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

/* Create a custom radio button */
.epaydz-checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 16px;
  width: 16px;
  background-color: #eee;
  border-radius: 50%;
}

/* On mouse-over, add a grey background color */
.epaydz-container:hover input ~ .epaydz-checkmark {
  background-color: #ccc;
}

/* When the radio button is checked, add a blue background */
.epaydz-container input:checked ~ .epaydz-checkmark {
  background-color: #2196F3;
}

/* Create the indicator (the dot/circle - hidden when not checked) */
.epaydz-checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the indicator (dot/circle) when checked */
.epaydz-container input:checked ~ .epaydz-checkmark:after {
  display: block;
}

/* Style the indicator (dot/circle) */
.epaydz-container .epaydz-checkmark:after {
 	top: 4px;
	left: 4px;
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: white;
}
</style>

<div class="col-12">

<label class="epaydz-container col-12"><p>Edahabia</p>
  <input type="radio" checked="checked" value="EDAHABIA" name="mode">
  <span class="epaydz-checkmark"></span>
</label>
</div>
<div class="col-12 mt-1">
<label class="epaydz-container"><p>CIB</p>
  <input type="radio" name="mode" value="CIB">
  <span class="epaydz-checkmark"></span>
</label>
</div>
</form>