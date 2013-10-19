<?php
/**
 * Apricot Model Factory class file
 *
 * @package Apricot
 */

namespace Apricot\Model;

use Apricot\KernelException;
use Apricot\Db\AdapterFactory;

/**
 * Factory
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Factory
{
    /**
     * Repository cache
     *
     * @var array
     */
    protected $_repositories = array();

    /**
     * Config
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Application namespace
     *
     * @var string
     */
    protected $_namespace = '';

    /**
     * Constructor
     *
     * @param mixed $config
     * @return void
     */
    public function __construct($config)
    {
        $this->setConfig($config);
    }

    /**
     * Set config
     *
     * @param mixed $config
     * @return \Apricot\Model\Factory
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * Get Config
     *
     * @return void
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Set application namespace
     *
     * @param string $namespace Namespace
     * @return \Apricot\Model\Factory
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }

    /**
     * Get namespace
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Get a repository
     *
     * @param mixed $name
     * @param mixed $connectionConfig
     * @param bool $cache
     * @return \Apricot\Model\Repository
     */
    public function getRepository($name, $connectionConfig = null, $cache = true)
    {
        if (!$cache 
            || ($cache && !isset($this->_repositories[$name]))
        ) {
            if (null == $connectionConfig) {
                $connectionConfig = $this->getConfig();
            }

            $namespace = $this->getNamespace() . '\\Model\\Repository\\';
            $className = $namespace . ucfirst($name);

            if (!class_exists($className)) {
                throw new FactoryException("Repository '$className' not found.", 33);
            }

            try {
                $adapter = AdapterFactory::makeAdapter($connectionConfig);
                $repository = new $className($adapter);
            } catch (\Exception $exception) {
                throw new FactoryException(
                    "Cannot load repository '$name'. "
                    . "Error: " . $exception->getCode() . ' '
                    . $exception->getMessage(), 33
                );
            }

            $repository->setFactory($this);

            if (!$cache) {
                return $repository;
            } else {
                $this->_repositories[$name] = $repository;
            }
        }

        return $this->_repositories[$name];
    }
}

/**
 * FactoryException
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class FactoryException extends \Exception
{
}
