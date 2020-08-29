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

namespace Chhiron\Sirio\Install;

use Configuration;
use Tools;

/**
 * Class Uninstaller - responsible for module installation process
 */
class Uninstaller extends AbstractInstaller
{
    /**
     * @var \Sirio
     */
    private $module;
    /**
     * @var array
     */
    private $configuration;

    /**
     * @param \Sirio $module
     * @param array $configuration
     */
    public function __construct(\Sirio $module, array $configuration)
    {
        $this->module = $module;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->uninstallConfiguration();

        if (!$this->uninstallDb()) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSqlStatements($fileName)
    {
        $sqlStatements = Tools::file_get_contents($fileName);
        $sqlStatements = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sqlStatements);

        return $sqlStatements;
    }

    private function uninstallConfiguration()
    {
        $configuration = $this->configuration['configuration'];

        if (empty($configuration)) {
            return;
        }

        foreach (array_keys($configuration) as $name) {
            if (!Configuration::deleteByName($name)) {
                continue;
            }
        }
    }

    /**
     * Executes sql in uninstall.sql file which is used for uninstalling
     *
     * @return bool
     *
     * @throws \Exception
     */
    private function uninstallDb()
    {
        $uninstallSqlFileName = $this->module->getLocalPath().'sql/uninstall/uninstall.sql';
        if (!file_exists($uninstallSqlFileName)) {
            return true;
        }

        $database = \Db::getInstance();
        $sqlStatements = $this->getSqlStatements($uninstallSqlFileName);
        return (bool) $this->execute($database, $sqlStatements);
    }
}
