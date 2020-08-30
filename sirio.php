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

class Sirio extends Module
{
    /**
     * If false, then SirioContainer is in immutable state
     */
    const DISABLE_CACHE = true;

    /**
     * @var SirioContainer
     */
    private $moduleContainer;

    public function __construct()
    {
        $this->name = 'sirio';
        $this->tab = 'analytics_stats';
        $this->version = '0.0.1';
        $this->author = 'Chiron';

        parent::__construct();
        $this->displayName = $this->l('Sirio');
        $this->description = $this->l('Sirio è un sistema di monitoraggio avanzato ideale per E-Commerce.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->module_key = '1d1be07cf291473029caea0c12939961';
    }


    public function install()
    {
        return parent::install() &&
        $this->registerHook('actionFrontControllerSetMedia') &&
        $this->registerHook('header');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }


    /**
     * Gets container with loaded classes defined in src folder
     *
     * @return SirioContainer
     */
    public function getContainer()
    {
        return $this->moduleContainer;
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerJavascript('remote-sirio', 'https://api.sirio.chiron.ai/api/v1/profiling', ['server' => 'remote', 'position' => 'bottom', 'priority' => 20]);
    }

    public function getContent()
    {
        /* Empty the Shop domain cache */
        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }
        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }
}
