<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Chiron, www.chiron.ai <support@chiron.ai>
 * @copyright Copyright (c) permanent, Chiron
 * @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 *  International Registered Trademark & Property of Chiron
 */


namespace Chiron\Sirio\Controller;

/**
 * Class AdminController - an abstraction for all admin controllers
 */
class AdminController extends \ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }
}
