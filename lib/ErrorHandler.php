<?php

namespace Kilab\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class ErrorHandler
{

    /**
     * ErrorHandler constructor.
     */
    public function __construct()
    {
        set_exception_handler([$this, 'exceptionHandler']);
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    /**
     * Handle Exception.
     *
     * @param Throwable $e
     *
     * @throws \Swift_SwiftException
     * @throws \LogicException
     */
    public function exceptionHandler(Throwable $e): void
    {
        $validHttpcodes = [200, 201, 202, 204, 302, 304, 400, 401, 403, 404, 406, 500];
        $jsonResponseCode = ($e->getCode() > 0 && in_array($e->getCode(), $validHttpcodes, true)) ? $e->getCode() : 500;
        $response = null;

        if (Config::get('Logger.Enabled')) {
            $context = $this->prepareContext($e->getCode(), $e->getFile(), $e->getLine(), $e->getTrace(),
                $e->getTraceAsString());

            Logger::instance()->error($e->getMessage(), $context);
        }

        if (Config::get('Debug') === true) {
            die(sprintf('[%s][%03d][EXCEPTION]: %s in %s line %d',
                date('H:i:s'),
                $e->getCode(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        }

        $response = new JsonResponse(['status' => false, 'msg' => $e->getMessage()], $jsonResponseCode);
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        $response->send();
        exit(1);
    }

    /**
     * Handle interpreter error.
     *
     * @param int    $number
     * @param string $message
     * @param string $file
     * @param int    $line
     * @param        $context
     *
     * @throws \Swift_SwiftException
     * @throws \LogicException
     */
    public function errorHandler(int $number, string $message, string $file, int $line, $context): void
    {
        if (Config::get('Logger.Enabled')) {
            $contextString = $context['traceString'] ?? '';
            $contextData = $this->prepareContext($number, $file, $line, $context, $contextString);

            Logger::instance()->error($message, $contextData);
        }

        if (Config::get('Debug') === true) {
            die(sprintf('[%s][%03d][%s]: %s in %s line %d',
                date('H:i:s'),
                $number,
                $this->getErrorLevelName($number),
                $message,
                $file,
                $line
            ));
        }

        $response = new JsonResponse(['status' => false, 'msg' => $message], 500);
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        $response->send();
        exit(1);
    }

    /**
     * Handle interpreter fatal error.
     *
     * @throws \Swift_SwiftException
     * @throws \LogicException
     */
    public function shutdownHandler(): void
    {
        $error = error_get_last();

        if ($error['type'] > 0) {
            if (Config::get('Logger.Enabled')) {
                $contextData = $this->prepareContext($error['type'], $error['file'], $error['line']);

                Logger::instance()->error($error['message'], $contextData);
            }

            if (Config::get('Debug') === true) {
                die(sprintf('[%s][%03d][%s]: %s in %s line %d',
                    date('H:i:s'),
                    $error['type'],
                    $this->getErrorLevelName($error['type']),
                    $error['message'],
                    $error['file'],
                    $error['line']
                ));
            }

            $response = new JsonResponse(['status' => false, 'msg' => $error['message']], 500);
            $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
            $response->send();
            exit(1);
        }
    }

    /**
     * Parse error or exception context to one format array.
     *
     * @param int         $code
     * @param string      $file
     * @param int         $line
     * @param array       $trace
     * @param string|null $traceString
     *
     * @return array
     */
    private function prepareContext(
        int $code,
        string $file,
        int $line,
        array $trace = [],
        string $traceString = null
    ): array {
        $kind = $this->getErrorLevelName($code);

        if ($kind === 'UNKNOWN ERROR' && !empty($trace)) {
            $kind = 'EXCEPTION';
        }

        $context = [
            'file'     => $file,
            'line'     => $line,
            'code'     => $code,
            'kind'     => $kind,
            'class'    => $trace[0]['class'] ?? null,
            'function' => $trace[0]['function'] ?? null,
            'trace'    => $traceString ?? null,
        ];

        return $context;
    }

    /**
     * Find PHP error level name by level number.
     *
     * @param int $level
     *
     * @return string
     */
    private function getErrorLevelName(int $level): string
    {
        $errorlevels = [
            E_ALL               => 'E_ALL',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_STRICT            => 'E_STRICT',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_NOTICE            => 'E_NOTICE',
            E_PARSE             => 'E_PARSE',
            E_WARNING           => 'E_WARNING',
            E_ERROR             => 'E_ERROR',
        ];

        return $errorlevels[$level] ?? 'UNKNOWN ERROR';
    }
}
