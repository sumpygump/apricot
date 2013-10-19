<?php
/**
 * Exception Handler Http
 *
 * @package Apricot
 */

namespace Apricot\Exception;

use \Apricot\Kernel;
use \Apricot\KernelException;
use \Apricot\Http\Request;

/**
 * ExceptionHandlerHttp
 *
 * @uses ExceptionHandler
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class ExceptionHandlerHttp extends ExceptionHandler
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
            array('\Apricot\Exception\ExceptionHandlerHttp', 'handleException')
        );

        set_error_handler(
            array('\Apricot\Exception\ExceptionHandlerHttp', 'handleError')
        );

        register_shutdown_function(
            array('\Apricot\Exception\ExceptionHandlerHttp', 'handleShutdown')
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

        $message .= self::renderTrace(debug_backtrace());

        if (self::$_kernel->getConfig('display_errors')) {
            printf(self::ERROR_MESSAGE_CAPSULE, $message);
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

        if (empty($error)) {
            return false;
        }

        // This way fatal errors will get logged as well.
        self::handleError(
            $error['type'], $error['message'],
            $error['file'], $error['line']
        );
    }

    /**
     * Handle thrown exceptions
     *
     * @param \Exception $exception The Exception object
     * @return void
     */
    public static function handleException(\Exception $exception)
    {
        $kernel = self::$_kernel;

        $httpCode = self::_getHttpStatusCode($exception);
        $header   = self::_getHeaderForStatusCode($httpCode);

        if (!headers_sent()) {
            header(
                "X-Apricot-Exception: "
                . $exception->getMessage()
                . " - " . self::getInformativeMessage($exception)
            );
            header($header);
        }

        if ($kernel->getRequest()->isXhr()) {
            $messages = array(
                'result'        => 'error',
                'error_message' => $exception->getMessage(),
                'error_details' => self::getInformativeMessage($exception),
            );

            echo json_encode($messages);
            return;
        }

        // Set up a request with the error information
        // so it can be dispatched to the error_controller
        $errorRequest = new Request(
            array(
                'controller' => 'error',
                'action'     => 'index',
                'httpcode'   => $httpCode,
                'exception'  => $exception,
            ),
            $kernel->getConfig()
        );

        try {
            // ATTEMPT 1: Re-dispatch error in layout.
            $controllerClassName = $kernel->loadController('error');
            $kernel->dispatch($errorRequest);
        } catch (\Exception $newException) {
            self::_handleExceptionNoLayout(
                $exception, $newException, $errorRequest
            );
        }
    }

    /**
     * Handle an exception where the application error controller failed
     *
     * @param \Exception $originalException Exception
     * @param \Exception $exception New Exception
     * @param \Apricot\Request $errorRequest Error request object
     * @return void
     */
    protected static function _handleExceptionNoLayout($originalException, $exception, $errorRequest)
    {
        $kernel = self::$_kernel;

        // If the problem wasn't that we couldn't load the Error controller
        if (!($exception->getCode()
            & KernelException::ERROR_LOAD_CONTROLLER)
        ) {
            self::_printDoubleException($originalException, $exception);
            return;
        }

        try {
            // ATTEMPT 2: Use the default Apricot Error Controller
            $controller = new \Apricot\Controller\ErrorController(
                $kernel, $errorRequest
            );
            $controller->execute();

            if ($originalException->getMessage() != $exception->getMessage()) {
                printf(
                    self::ERROR_MESSAGE_CAPSULE,
                    'Additionally, ' . $exception->getMessage() . ', '
                    . self::getInformativeMessage($exception)
                );
            }
        } catch (\Exception $failsafeException) {
            // Something horrible happened
            printf(
                self::EXCEPTION_MESSAGE_CAPSULE,
                'FAILSAFE: ' . $failsafeException->getMessage(),
                self::displayException($failsafeException)
            );

            // Tell us about the other two exceptions too
            self::_printDoubleException($originalException, $exception);
        }
    }

    /**
     * Print two exceptions
     *
     * This is used when there is an exception when trying to handle an 
     * exception.
     *
     * @param \Exception $originalException Original exception thrown
     * @param \Exception $exception New exception thrown
     * @return void
     */
    protected static function _printDoubleException($originalException, $exception)
    {
        printf(
            self::EXCEPTION_MESSAGE_CAPSULE,
            $originalException->getMessage(),
            self::displayException($originalException)
        );

        printf(
            self::ERROR_MESSAGE_CAPSULE,
            'Additionally, ' . $exception->getMessage() . ', '
            . self::getInformativeMessage($exception)
        );
    }

    /**
     * Display exception
     *
     * @param mixed $e Exception object
     * @return string
     */
    public static function displayException($e)
    {
        $out  = "";
        $out .= "<p class=\"errorPage-text\">"
            . get_class($e) . ": "
            . self::getInformativeMessage($e) . "</p>";

        $trace = $e->getTrace();

        $out .= self::renderTrace($trace);

        return $out;
    }

    /**
     * Render trace
     *
     * @param array $trace Debug backtrace
     * @return string HTML
     */
    public static function renderTrace($trace)
    {
        $out  = "";
        $out .= "<h2 class=\"hdg hdg_2 hdg_errorInformation\">Stack Trace:</h2>";

        $out  .=  "<div class=\"errorPage-tableWrapper\">"
            . "<table class=\"table table-bordered table-condensed table-striped\">"
            . "<tr><th>#</th>"
            . "<th>function</th>"
            . "<th>location</th>"
            . "<th>args</th></tr>";

        foreach ($trace as $i => $tl) {
            $file  = isset($tl['file']) ? $tl['file'] : '';
            $class = isset($tl['class']) ? $tl['class'] : 'main';
            $line  = isset($tl['line']) ? $tl['line'] : '0';
            $out  .= "<tr>"
                . "<td>" . $i . "</td>"
                . "<td>" . $class . "::" .  $tl['function'] . "()</td>"
                . "<td>" . $file  . ":" . $line . "</td>";

            if (isset($tl['args'])) {
                $out .= "<td>" . self::renderTraceArgs($tl['args']) . "</td>";
            } else {
                $out .= "<td>&nbsp;</td>";
            }

            $out .= "</tr>\n";
        }

        $out .= "</table></div>";

        return $out;
    }

    /**
     * Log an exception
     *
     * @param mixed $exception The Exception object
     * @return void
     */
    public static function logException($exception)
    {
        $kernel = self::$_kernel;

        $logFile    = "exceptions";
        $requestUri = $kernel->getRequest()->getEnvironmentParam('REQUEST_URI');
        $userAgent  = $kernel->getRequest()->getHeaderParam('USER_AGENT');
        $referer    = $kernel->getRequest()->getHeaderParam('REFERER');

        $logErrorNumber = self::_generateRandomString();

        $logMessage = "---------------------------------------\n"
            . "ERROR NUMBER: $logErrorNumber\n"
            . "DATE: " . date('Y-m-d H:i:s') . "\n"
            . "EXCEPTION TYPE: " . get_class($exception) . "\n"
            . "MESSAGE: " . $exception->getMessage() . "\n"
            . "CODE: " . $exception->getCode() . "\n"
            . "HTTPCODE: " . self::_getHttpStatusCode($exception) . "\n"
            . (empty($requestUri) ? '' : "REQUEST URI: " . $requestUri . "\n")
            . (empty($referer) ? '' : "REFERER: " . $referer . "\n")
            . (empty($userAgent) ? '' : "HTTP USER AGENT: " . $userAgent . "\n")
            . "TRACE:\n" . self::renderTraceTextTable($exception->getTrace()) . "\n"
            ;

        self::$_kernel->log($logMessage, $logFile);

        return $logErrorNumber;
    }

    /**
     * Get the correct http code from the exception
     *
     * @param object $e The Exception object
     * @return int
     */
    protected static function _getHttpStatusCode($e)
    {
        if ($e->getCode() == 0) {
            return 500;
        }

        if ($e->getCode() < 100 & KernelException::ERROR_LOAD) {
            return 404;
        }

        return $e->getCode();
    }

    /**
     * Get the correct http header status code string
     *
     * @param int $code The http status code
     * @return string
     */
    protected static function _getHeaderForStatusCode($code)
    {
        $headers = array (
            200 => 'HTTP/1.0 200 OK',
            303 => 'HTTP/1.0 303 See Other',
            304 => 'HTTP/1.0 304 Not Modified',
            401 => 'HTTP/1.0 401 Unauthorized',
            404 => 'HTTP/1.0 404 Not Found',
            403 => 'HTTP/1.0 403 Forbidden',
            405 => 'HTTP/1.0 405 Method not allowed',
            500 => 'HTTP/1.0 500 Server Error',
            503 => 'HTTP/1.0 503 Service Unavailable',
        );

        if (!isset($headers[$code])) {
            $code = 500;
        }

        return $headers[$code];
    }

    const ERROR_MESSAGE_CAPSULE = '<div style="margin:4px;padding:8px;color:#c09853;background-color:#fcf8c3;border:1px solid #fbeed5;border-radius:4px;">%s</div>';
    const EXCEPTION_MESSAGE_CAPSULE = '<div style="margin:4px;padding:8px;color:#b94a48;background-color:#f2dede;border:1px solid #eed3d7;border-radius:4px;"><p style="margin:0;font-size:24px;font-weight:bold;">Error: %s</p>%s</div>';
}
