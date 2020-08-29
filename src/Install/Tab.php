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

namespace Chiron\Sirio\Install;

/**
 * Class Tab - module admin tab settings
 */
class Tab
{
    /**
     * @var string info controller name
     */
    private $controllerInfo = 'AdminSirioInfo';

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getControllerInfo()
    {
        return $this->controllerInfo;
    }

    /**
     * @return array
     */
    public function getTabs()
    {
        return isset($this->configuration['tabs']) ? $this->configuration['tabs'] : [];
    }
}
