<?php
/**
 * Apricot Module Logger class file 
 *
 * @package Apricot\Module
 */

namespace Apricot\Kernel\Extension;

use \Apricot\Kernel\Extension;

/**
 * Apricot Module Logger class
 * 
 * @uses Apricot_Kernel_Abstract
 * @package Apricot\Module
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Logger extends Extension
{
    /**
     * Whether logger is enabled
     * 
     * @var bool
     */
    protected $_enabled = false;

    /**
     * Config object
     * 
     * @var mixed
     */
    protected $_config = null;

    /**
     * The current request object
     * 
     * @var mixed
     */
    protected $_request = null;

    /**
     * Handle options
     * 
     * @param array $options Options passed in constructor
     * @return void
     */
    public function handleOptions($options)
    {
        if (isset($options->enabled)) {
            $this->setEnabled($options->enabled);
        }
    }

    /**
     * Set enabled status
     * 
     * @param bool $value Value
     * @return void
     */
    public function setEnabled($value = true)
    {
        $this->_enabled = (bool) $value;
    }

    /**
     * Set configuration object
     * 
     * @param object $config Configuration object
     * @return void
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * Set the request object
     * 
     * @param object $request Request object
     * @return void
     */
    public function setRequest($request)
    {
        $this->_request = $request;
    }

    /**
     * Register this module
     * 
     * @return void
     */
    public function register()
    {
        $this->setConfig($this->_kernel->getConfig());

        if (isset($this->_options->enabled)) {
            $enabled = $this->_options->enabled;
        } else {
            $enabled = false;
        }

        if ($enabled && $enabled !== "false") {
            $this->setEnabled(true);
        }

        $this->setRequest($this->_kernel->getRequest());
    }

    /**
     * Direct call
     * 
     * @param mixed $args Arguments
     * @return mixed
     */
    public function direct($args = null)
    {
        $args = func_get_args();
        return call_user_func_array(array($this, 'log'), $args);
    }

    /**
     * Write data to log file
     *
     * If logFile is null, the filename will be based on the controller name
     *
     * @param array|string $input The text to be logged
     * @param string $logFile The filename to which to write.
     * @return bool Whether the message was written to the logfile
     */
    public function log($input, $logFile = null)
    {
        if (!$this->_enabled) {
            return false;
        }

        $path = $this->_getLogDir();

        if ($path === false) {
            return false;
        }

        if (!is_writable($path)) {
            return false;
        }

        // Get log file name
        $logFile = $this->_getCorrectLogFile($logFile);

        if (is_array($input) || is_object($input)) {
            $input = "\n" . print_r($input, 1);
        }

        file_put_contents(
            $path . $logFile . ".log",
            date('Y-m-d H:i:s') . "\t" . $input . "\n",
            FILE_APPEND
        );

        @chmod($path . $logFile . ".log", 0666);
        return true;
    }

    /**
     * Get the correct log directory
     *
     * @return mixed
     */
    protected function _getLogDir()
    {
        $log_dir = $this->_config->get('log_dir');

        // ensure ending slash on log_dir
        if (substr($log_dir, -1) != '/' && substr($log_dir, -1) != '\\') {
            $log_dir .= DIRECTORY_SEPARATOR;
        }

        // Find the correct logdir (exists)
        $path = $log_dir;
        if (!file_exists($log_dir)) {
            $path = $this->_config->get('app_root')
                . DIRECTORY_SEPARATOR . $log_dir;
            if (!file_exists($path)) {
                $path = dirname($this->_config->get('app_root'))
                    . DIRECTORY_SEPARATOR . $log_dir;
                if (!file_exists($path)) {
                    return false;
                }
            }
        }

        return $path;
    }

    /**
     * Get the correct log file name
     *
     * @param mixed $logFile log filename
     * @return void
     */
    protected function _getCorrectLogFile($logFile = null)
    {
        if ($logFile == null) {
            $logFile = $this->_request->controller;
            if ($logFile == '') {
                $logFile = 'log';
            } else {
                $logFile = "controller." . $logFile;
            }
        }

        return $logFile;
    }

    /**
     * Clear log file
     *
     * @param mixed $logFile The log file to clear (if true, clear all)
     * @return void
     */
    public function clearLog($logFile = null)
    {
        $path = $this->_getLogDir();

        if ($path === false) {
            return false;
        }

        if ($logFile === true) {
            $files = glob($path . '*.log');
            foreach ($files as $file) {
                @unlink($file);
            }
        } else {
            $logFile = $this->_getCorrectLogFile($logFile);
            @unlink($path . $logFile . '.log');
        }
    }
}
