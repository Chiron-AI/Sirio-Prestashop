{**
 * 2007-2020 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{**
* Copyright (c) 2019 Nexi Payments S.p.A.
*
* @author      iPlusService S.r.l.
* @copyright   Copyright (c) 2019 Nexi Payments S.p.A. (https://ecommerce.nexi.it)
* @license     GNU General Public License v3.0
* @category    Payment Module
* @package     Nexi XPay
* @version     5.0.0
*}
<div class="panel">
<form class="form-horizontal" method="post">

   <ps-panel header="{l s='Settings Sirio' mod='sirio'}">
      <div class="form-wrapper">
         <ps-switch help="{l s='Enable Sirio Module' mod='sirio'}" name="SIRIO_MODULE_ENABLE" label="{l s='Enable' mod='sirio'}" yes="Yes" no="No"  active="{if $SIRIO_MODULE_ENABLE==1}true{else}false{/if}"></ps-switch>
      </div>
      <ps-panel-footer>

         <ps-panel-footer-submit name="btnSubmit" icon="process-icon-save" title="{l s='Save' mod='sirio'}" direction="right"></<ps-panel-footer-submit>
      </ps-panel-footer>
   </ps-panel>

</form>
</div>

