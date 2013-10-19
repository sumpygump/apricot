<?php
/**
 * Extension Manager
 *
 * @package Apricot
 */

namespace Apricot\Extension;

use \Apricot\KernelException;

/**
 * Extension manager
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class Manager
{
    /**
     * Namespace expected for extensions managed by this class
     *
     * @var string
     */
    protected $_namespace = '';

    /**
     * Path where the extensions should be found
     *
     * @var string
     */
    protected $_path = '';

    /**
     * Storage for instances of extensions
     *
     * @var array
     */
    protected $_extensions = array();

    /**
     * Constructor
     *
     * @param string $namespace Namespace for extensions managed by this class
     * @param string $path Path where extensions can be found
     * @return void
     */
    public function __construct($namespace, $path)
    {
        $this->setNamespace($namespace);
        $this->setPath($path);
    }

    /**
     * Set the namespace
     *
     * @param string $namespace
     * @return \Apricot\Extension\Manager
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
     * Set the path to the extension source files
     *
     * @param string $path Path
     * @return \Apricot\Extension\Manager
     */
    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Make an extension class
     *
     * Allow for different options passed into the constructors for different 
     * extension managers
     *
     * This should be implemented in a child class
     *
     * @param string $className Name of class
     * @param mixed $options Options for constructor
     * @return \Apricot\Extension\ExtensionInterface
     */
    public function makeExtension($className, $options = array())
    {
    }

    /**
     * Import extensions
     *
     * Load extensions that have options so the options can be set
     * 
     * @param \StdClass $extensionsOptions List of extensions
     * @return bool
     */
    public function importExtensions($extensionsOptions)
    {
        if (empty($extensionsOptions)) {
            return false;
        }

        foreach ($extensionsOptions as $name => $extensionOptions) {
            $extension = $this->getExtension($name, $extensionOptions);
        }
    }

    /**
     * Get an extension by name
     * 
     * @param string $name Name of extension
     * @param array $options Options array to pass to extension
     * @return \Apricot\ExtensionInterface
     */
    public function getExtension($name, $options = array())
    {
        $name = ucfirst($name);

        if (!isset($this->_extensions[$name])) {
            // Handle special case where extension options define the exact 
            // location of extensions
            if (isset($options->class)) {
                if (!class_exists($options->class)) {
                    if (isset($options->path)) {
                        $extension = $this->getExtensionOutsidePath($name, $options);
                        if ($extension) {
                            return $extension;
                        }
                    }
                }
                $className = $options->class;
            } else {
                $className = $this->_namespace . '\\' . $name;
            }

            // Attempt to autoload first
            if (!class_exists($className)) {
                if (!$this->loadExtension($name)) {
                    return false;
                }
            }

            $extension = $this->makeExtension($className, $options);

            $extension->register();
            $this->_extensions[$name] = $extension;
        }

        return $this->_extensions[$name];
    }

    /**
     * Attempt to get extension outside path
     *
     * @param string $name Name of extension
     * @param mixed $options Options
     * @return \Apricot\ExtensionInterface
     */
    public function getExtensionOutsidePath($name, $options)
    {
        if (! @include_once $options->path) {
            throw new KernelException(
                "Cannot load extension '" . $options->path . "'. "
            );
        }

        if (!class_exists($options->class)) {
            return false;
        }

        $className = $options->class;
        $extension = $this->makeExtension($className, $options);

        $extension->register();
        $this->_extensions[$name] = $extension;

        return $this->_extensions[$name];
    }

    /**
     * Load extension
     *
     * @param string $name Extension name
     * @return void
     */
    public function loadExtension($name)
    {
        $classFile = $this->_path . ucfirst($name) . ".php";

        if (! include_once $classFile) {
            error_log("Attempt to load extension '$name' failed.");
            return false;
        }

        return true;
    }

    /**
     * Is extension loaded
     *
     * @param string $name Extension name
     * @return bool
     */
    public function isExtension($name)
    {
        return isset($this->_extensions[$name]);
    }

    /**
     * Magic get method to retrieve an extension object
     *
     * @param string $property Name of extension
     * @return \Apricot\Extension
     */
    public function __get($property)
    {
        return $this->getExtension($property);
    }

    /**
     * Magic call method
     *
     * This will call an extension directly (calls the direct method)
     *
     * @param string $method Name of method invoked
     * @param array $args Arguments passed to method
     * @return mixed
     */
    public function __call($method, $args)
    {
        $extension = $this->getExtension($method);

        return call_user_func_array(array($extension, 'direct'), $args);
    }
}
