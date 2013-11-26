<?php
/**
 * Apricot Loader class file
 *
 * @package Apricot
 */

namespace Apricot;

/**
 * Apricot Autoloader class
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Loader
{
    /**
     * Whether this autoloader has been registered
     *
     * @var bool
     */
    protected static $_isRegistered = false;

    /**
     * Default paths that will be searched for classes
     *
     * The asterisk has no special meaning, it is just a place holder where the 
     * application namespace would go.
     *
     * @var array
     */
    protected static $_paths = array(
        'lib',
        '*' => 'app',
        'app/controllers',
        'app/models',
        'app/controllers/extensions',
        'app/views/extensions',
        '*\\Model\\Entity' => 'app/models/entities',
        '*\\Model\\Repository' => 'app/models/repositories',
    );

    /**
     * Storage for the stack of realpaths
     *
     * @var array
     */
    protected static $_realPaths = array();

    /**
     * Register this autoloader with PHP
     *
     * @return void
     */
    public static function register()
    {
        self::_makeRealPaths();
        spl_autoload_register(array('Apricot\Loader', 'autoload'));
        self::$_isRegistered = true;
    }

    /**
     * Add a path to the list of paths to search for classes
     *
     * @param string $path Path to add
     * @param string $namespace Namespace of class to add
     * @param bool $prepend Add to front of stack
     * @return void
     */
    public static function addPath($path, $namespace = '', $prepend = false)
    {
        if (in_array($path, array_values(self::$_paths))) {
            return false;
        }

        if ($namespace) {
            self::$_paths[$namespace] = $path;
        } else {
            if ($prepend) {
                array_unshift(self::$_paths, $path);
            } else {
                self::$_paths[] = $path;
            }
        }

        // If we are already registered, we need to re generate the real paths
        if (self::$_isRegistered) {
            self::_makeRealPaths();
        }
    }

    /**
     * The autoloader function
     *
     * Runs through the list of real paths stored and attempts to find the 
     * class file. If it exists, it is automatically include_onced
     *
     * @param string $className Class that was attempted to be loaded
     * @return bool
     */
    public static function autoload($className)
    {
        $classPath = str_replace(
            array('\\', '_'),
            array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
            $className
        );

        foreach (self::getPaths() as $namespace => $path) {
            if (!is_numeric($namespace)) {
                // if namespace is set, then we should use the last section as 
                // the classpath instead.
                $classPath = basename($classPath);
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $classPath . ".php";
            if (is_file($fullPath)) {
                include_once $fullPath;
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a list of realpaths from the paths defined
     *
     * @return void
     */
    protected static function _makeRealPaths()
    {
        $realpaths = array();

        // Support autoloader to work when composed in and the Apricot lib is 
        // in a different dir than the app classes to be autoloaded
        if (defined('APP_ROOT')) {
            $root = APP_ROOT;

            // build paths from the root and only include them if they exist
            foreach (self::$_paths as $name => $path) {
                $realpath = $root . DIRECTORY_SEPARATOR . $path;
                if (file_exists($realpath)) {
                    $realpaths[$name] = $realpath;
                }
            }
        }

        // Determine the app root from this file
        // TODO: Do something more robust to find the root
        $root = dirname(dirname(dirname(__FILE__)));

        // build paths from the root and only include them if they exist
        foreach (self::$_paths as $name => $path) {
            $realpath = $root . DIRECTORY_SEPARATOR . $path;
            if (file_exists($realpath)) {
                $realpaths[$name] = $realpath;
            }
        }

        self::$_realPaths = $realpaths;
    }

    /**
     * Get defined (real) paths where classes should be looked
     *
     * @return array
     */
    public static function getPaths()
    {
        return self::$_realPaths;
    }
}
