<?php
/**
 * Apricot Exception Handler Console class file
 *
 * @package Apricot
 */

namespace Apricot\Exception;

use \Apricot\Kernel;
use \Apricot\KernelException;

/**
 * Exception Handler Console
 *
 * @uses ExceptionHandler
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class ExceptionHandlerConsole extends ExceptionHandler
{
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
            array('\Apricot\Exception\ExceptionHandlerConsole', 'handleException')
        );

        set_error_handler(
            array('\Apricot\Exception\ExceptionHandlerConsole', 'handleError')
        );

        register_shutdown_function(
            array('\Apricot\Exception\ExceptionHandlerConsole', 'handleShutdown')
        );
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
            $terminal = self::$_kernel->getTerminal();

            $terminal->setaf(1);
            print $message . "\n";
            $terminal->op();
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
        $terminal    = self::$_kernel->getTerminal();

        print "\n";
        $terminal->pretty_message(
            'Exception: ' . $exception->getMessage() . " (Logged as $errorNumber)", 7, 1
        );
        print "\n";
    }

    /**
     * Log an exception
     *
     * @param \Exception $exception The Exception object
     * @return void
     */
    public static function logException($exception)
    {
        $logFile = "exceptions-terminal";

        $logErrorNumber = self::_generateRandomString();

        $logMessage = "---------------------------------------\n"
            . "ERROR NUMBER: $logErrorNumber\n"
            . "DATE: " . date('Y-m-d H:i:s') . "\n"
            . "EXCEPTION TYPE: " . get_class($exception) . "\n"
            . "MESSAGE: " . $exception->getMessage() . "\n"
            . "CODE: " . $exception->getCode() . "\n"
            . "TRACE:\n" . self::renderTraceTextTable($exception->getTrace()) . "\n"
            ;

        self::$_kernel->log($logMessage, $logFile);

        return $logErrorNumber;
    }
}
