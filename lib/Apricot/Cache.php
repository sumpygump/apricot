<?php
/**
 * Apricot Cache file
 * <pre>
 *     _               _           _
 *    / \   _ __  _ __(_) ___ ___ | |_
 *   / _ \ | '_ \| '__| |/ __/ _ \| __|
 *  / ___ \| |_) | |  | | (_| (_) | |_
 * /_/   \_\ .__/|_|  |_|\___\___/ \__|
 *         |_|
 * </pre>
 *
 * @package Apricot
 * @version $Id$
 */

namespace Apricot;

use \Apricot\Kernel;

/**
 * Apricot_Cache
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Cache
{
    /**
     * The \Apricot\Kernel object
     *
     * @var \Apricot\Kernel
     */
    protected $_kernel;

    /**
     * Request object
     * 
     * @var mixed
     */
    protected $_request;

    /**
     * The name of the cachefile name for this request
     * 
     * @var mixed
     */
    protected $_cachefilename;

    /**
     * Constructor
     * 
     * @param \Apricot\Kernel $kernel The Kernel object
     * @return void
     */
    public function __construct(Kernel $kernel)
    {
        $this->_kernel = $kernel;
    }

    /**
     * Start the cache process
     * 
     * @param mixed $request A request object
     * @return bool
     */
    public function start($request)
    {
        $this->_request = $request;

        // Only use the cache for GET requests
        if (!$this->_isGetMethod()) {
            return false;
        }

        $this->_cachefilename = $this->getCacheFilename($this->_request);

        if (file_exists($this->_cachefilename)) {
            echo "<!-- Cached file -->\n";
            include $this->_cachefilename;
            return $this->_kernel->exitApplication();
        }

        ob_start();
        return true;
    }

    /**
     * End cache processing
     * 
     * @return bool Whether cache file was written
     */
    public function end()
    {
        if ($this->_cachefilename) {
            file_put_contents($this->_cachefilename, ob_get_contents());
            ob_end_flush();
            return true;
        }

        return false;
    }

    /**
     * Whether the request type is a get
     * 
     * @return bool
     */
    protected function _isGetMethod()
    {
        if ($this->_request->getEnvironmentParam('request_method') == 'POST') {
            return false;
        }
        return true;
    }

    /**
     * Get the cachefile name for a given request
     * 
     * @param mixed $request Request object or array
     * @return void
     */
    public function getCacheFilename($request)
    {
        $dir = $this->_getCacheDir();

        if (is_object($request)) {
            $a = $request->toArray();
        } else {
            $a = $request;
        }

        return $dir . DIRECTORY_SEPARATOR . md5(serialize($a));
    }

    /**
     * Clear cache
     *
     * Clear the entire cache or optionally one request's file
     * 
     * @param mixed $request A request object or string
     * @return void
     */
    public function clear($request = '')
    {
        if ($request == '') {
            $files = glob($this->_getCacheDir() . DIRECTORY_SEPARATOR . '*');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            $filename = $this->getCacheFilename($request);
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * Get the dir for the cache files
     * 
     * @return string
     */
    protected function _getCacheDir()
    {
        if ($this->_kernel->getConfig('cache_dir')) {
            $dir = $this->_kernel->getConfig('app_root')
                . DIRECTORY_SEPARATOR
                . $this->_kernel->getConfig('cache_dir');
        } else {
            // default to something sensible
            $dir = $this->_kernel->getConfig('app_root')
                . DIRECTORY_SEPARATOR
                . 'cache';
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }

        return $dir;
    }
}
