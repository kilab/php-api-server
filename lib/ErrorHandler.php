<?php

namespace Kilab\Api;

use Throwable;
use Kilab\Api\Response\JsonResponse;

class ErrorHandler
{
    /**
     * $_SERVER superglobal array content.
     *
     * @var array
     */
    private $serverInfo = [];

    /**
     * ErrorHandler constructor.
     *
     * @param array $serverInfo
     */
    public function __construct(array $serverInfo)
    {
        $this->serverInfo = $serverInfo;

        if (Env::get('DEBUG') === false) {
            set_exception_handler([$this, 'exceptionHandler']);
            set_error_handler([$this, 'errorHandler']);
            register_shutdown_function([$this, 'fatalErrorHandler']);
        }
    }

    /**
     * Handle Exception.
     *
     * @param Throwable $e
     *
     * @return JsonResponse
     * @throws \LogicException
     */
    public function exceptionHandler(Throwable $e): JsonResponse
    {
        $validHttpcodes = ['200', '201', '202', '204', '302', '304', '400', '401', '403', '404', '406', '500'];

        $errorMessage = $e->getMessage() . '.';
        $code = ($e->getCode() > 0 && in_array($e->getCode(), $validHttpcodes)) ? $e->getCode() : 500;

        if (Config::get('Logger.Enabled')) {
            $this->logError($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        }

        return new JsonResponse(['status' => false, 'msg' => $errorMessage], $code);
    }

    /**
     * Handle interpreter error.
     *
     * @param int $number
     * @param string $message
     * @param string $file
     * @param int $line
     * @param        $context
     *
     * @return JsonResponse
     */
    public function errorHandler(int $number, string $message, string $file, int $line, $context): JsonResponse
    {
        $errorMessage = 'ERROR: ' . $message . ' in ' . $file . ', line: ' . $line;

        if (Config::get('Logger.Enabled')) {
            $this->logError($message, $file, $line, $context);
        }

        return new JsonResponse(['status' => false, 'msg' => $errorMessage], 500);
    }

    /**
     * Handle interpreter fatal error.
     *
     * @return JsonResponse|null
     */
    public function fatalErrorHandler(): ?JsonResponse
    {
        $error = error_get_last();

        if ($error['type'] > 0) {
            $errorMessage = 'FATAL ERROR: ' . $error['message'] . ' in ' . $error['file'] . ', line: ' . $error['line'];

            if (Config::get('Logger.Enabled')) {
                $this->logError($error['message'], $error['file'], $error['line']);
            }

            return new JsonResponse(['status' => false, 'msg' => $errorMessage], 500);
        }

        return null;
    }

    /**
     * Store occured error in logs.
     *
     * @param string $message
     * @param string $file
     * @param int $line
     * @param        $trace
     */
    public function logError(string $message, string $file, int $line, $trace = null): void
    {
        if ($trace) {
            $trace = preg_replace('/^.+/m', '            $0', $trace);
        }

        $content = $message . ' | ' . $file . ' [L: ' . $line . '] | ' . $this->serverInfo['REMOTE_ADDR'] . PHP_EOL . $trace;

        Logger::log($content);
    }
}
