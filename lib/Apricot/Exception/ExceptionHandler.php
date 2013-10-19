<?php
/**
 * Apricot Exception Handler file
 *
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
 * @subpackage ExceptionHandler
 * @version $Id: ExceptionHandler.php 1734 2010-03-16 02:49:55Z jansen $
 */

namespace Apricot\Exception;

use \Apricot\Kernel;
use \Apricot\KernelException;

/**
 * Apricot_ExceptionHandler
 *
 * @package Apricot
 * @subpackage ExceptionHandler
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class ExceptionHandler
{
    /**
     * Storage of kernel object
     *
     * @var object
     */
    protected static $_kernel = null;

    /**
     * Init the error handlers
     *
     * @param Kernel $kernel Kernel object
     * @return void
     */
    public static function initHandlers(Kernel $kernel)
    {
        self::setKernel($kernel);

        set_exception_handler(
            array('\Apricot\Exception\ExceptionHandler', 'handleException')
        );

        set_error_handler(
            array('\Apricot\Exception\ExceptionHandler', 'handleError')
        );

        register_shutdown_function(
            array('\Apricot\Exception\ExceptionHandler', 'handleShutdown')
        );
    }

    /**
     * Restore original error and exception handlers
     *
     * @return void
     */
    public static function releaseHandlers()
    {
        restore_exception_handler();
        restore_error_handler();
    }

    /**
     * Set the kernel object
     *
     * @param \Apricot\Kernel $kernel Apricot Kernel object
     * @return void
     */
    public static function setKernel($kernel)
    {
        self::$_kernel = $kernel;
    }

    /**
     * Handle an error
     *
     * @return void
     */
    public static function handleError()
    {
        list($errno, $message, $file, $line) = func_get_args();

        $message = self::_convertErrorCode($errno)
            . ": " . $message . " in " . $file . ":" . $line;

        if (self::$_kernel->getConfig('display_errors')) {
            print $message;
        }

        self::$_kernel->log($message, 'errors');
    }

    /**
     * Handle a shutdown
     *
     * @return void
     */
    public static function handleShutdown()
    {
        $error = error_get_last();

        if (!empty($error)) {
            // This way fatal errors will get logged as well.
            self::handleError(
                $error['type'], $error['message'],
                $error['file'], $error['line']
            );
        }
    }

    /**
     * Handle thrown exceptions
     *
     * @param \Exception $exception The Exception object
     * @return void
     */
    public static function handleException(\Exception $exception)
    {
        $errorNumber = self::logException($exception);
    }

    /**
     * Get the message from the exception that includes the file and line number
     *
     * @param \Exception $exception Exception object
     * @return string
     */
    public static function getInformativeMessage($exception)
    {
        return "Error code #" . $exception->getCode()
            . " in file " . $exception->getFile()
            . " on line " . $exception->getLine() . ".";
    }

    /**
     * Convert an error code into the PHP error constant name
     *
     * @param int $code The PHP error code
     * @return string
     */
    protected static function _convertErrorCode($code)
    {
        $errorLevels = array(
            1     => 'E_ERROR',
            2     => 'E_WARNING',
            4     => 'E_PARSE',
            8     => 'E_NOTICE',
            16    => 'E_CORE_ERROR',
            32    => 'E_CORE_WARNING',
            64    => 'E_COMPILE_ERROR',
            128   => 'E_COMPILE_WARNING',
            256   => 'E_USER_ERROR',
            512   => 'E_USER_WARNING',
            1024  => 'E_USER_NOTICE',
            2048  => 'E_STRICT',
            4096  => 'E_RECOVERABLE_ERROR',
            8192  => 'E_DEPRECATED',
            16384 => 'E_USER_DEPRECATED',
        );

        return $errorLevels[$code];
    }

    /**
     * Log an exception
     *
     * @param \Exception $exception The Exception object
     * @return void
     */
    public static function logException($exception)
    {
        // To be implemented in child class
    }

    /**
     * Trace Text Table
     *
     * @param mixed $trace The trace array
     * @return string
     */
    public static function renderTraceTextTable($trace)
    {
        $out = '';

        $out .= "#\tFunction\tLocation\tArgs\n";

        foreach ($trace as $i => $tl) {
            $class = isset($tl['class']) ? $tl['class'] : 'main';
            $file  = isset($tl['file']) ? $tl['file'] : '';
            $line  = isset($tl['line']) ? $tl['line'] : '0';
            $out  .= $i . "\t"
                . $class . "::" . $tl['function'] . "()\t"
                . $file . ":" . $line . "\t"
                . self::renderTraceArgs($tl['args'], ",")
                . "\n";
        }

        return $out;
    }

    /**
     * Display Trace Args
     *
     * @param mixed $args Arguments to display
     * @param string $glue The glue used to implode() the args if array
     * @return string
     */
    public static function renderTraceArgs($args, $glue="\n")
    {
        $out = '';

        if (is_array($args)) {
            foreach ($args as $arg) {
                if (is_object($arg)) {
                    $out .= get_class($arg) . $glue;
                } else {
                    if (is_array($arg)) {
                        $arg = 'Array';
                    }
                    $out .= $arg . $glue;
                }
            }
        } else {
            $out .= $args;
        }

        return $out;
    }

    /**
     * Generate a random string that can be used as an error code
     *
     * @param int $length The length of the filename
     * @return string
     */
    protected static function _generateRandomString($length=4)
    {
        $characters = 'abcdefghijkmnpqrstuvwxyz0123456789';

        $string = '';
        for ($i = 0; $i < $length; ++$i) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }
}
