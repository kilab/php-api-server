<?php

namespace Kilab\Api;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class Logger extends AbstractLogger implements LoggerInterface
{

    protected static $instance;

    /**
     * Get Logger class instance.
     *
     * @return Logger
     */
    public static function instance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    /**
     * Call incident logging.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @throws \Swift_SwiftException
     */
    public function log($level, $message, array $context = [])
    {
        $message = (string)$message;
        $compactMessage = $this->formatMessage($message, $context);

        if ($level !== LogLevel::DEBUG) {
            $this->writeFile($compactMessage, $level);
        }

        if ($level === LogLevel::DEBUG) {
            $this->writeStdOutput($message);
        }

        if ($level === LogLevel::ALERT) {
            $this->sendMail($compactMessage);
        }
    }

    /**
     * Format incident details to string.
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
     */
    private function formatMessage(string $message, array $context): string
    {
        $message = sprintf("[%s][%03d][%s]: %s in %s line %d \n%s",
            date('H:i:s'),
            $context['code'] ?? null,
            $context['kind'] ?? null,
            $message,
            $context['file'] ?? null,
            $context['line'] ?? null,
            preg_replace('/^.+/m', '          $0', $context['trace'] ?? null)
        );

        return $message;
    }

    /**
     * Store error info in log file.
     *
     * @param string $message
     * @param string $level
     *
     * @return bool
     */
    private function writeFile(string $message, string $level): bool
    {
        $directory = BASE_DIR . 'logs/' . date('Y');
        if (!is_dir($directory)) {
            mkdir($directory, 02777);
            chmod($directory, 02777);
        }

        $directory .= DIRECTORY_SEPARATOR . date('m');
        if (!is_dir($directory)) {
            mkdir($directory, 02777);
            chmod($directory, 02777);
        }

        $filename = $directory . DIRECTORY_SEPARATOR . date('d') . '-' . API_VERSION . '-' . $level . '.log';
        if (!file_exists($filename)) {
            touch($filename);
            chmod($filename, 0777);
        }

        return (bool)file_put_contents($filename, $message . PHP_EOL, FILE_APPEND | LOCK_EX);

    }

    /**
     * Write incident message to STDOUT.
     *
     * @param string $message
     *
     * @return bool
     */
    private function writeStdOutput(string $message): bool {
        fwrite(STDOUT, $message.PHP_EOL);

        return true;
    }

    /**
     * Send error message to specified in config email address.
     *
     * @param string $message
     *
     * @return bool
     * @throws \Swift_SwiftException
     */
    private function sendMail(string $message): bool
    {
        if (!Config::get('Logger.Mail.Enabled')) {
            return false;
        }

        $transport = (new Swift_SmtpTransport(Config::get('Logger.Mail.Host'), Config::get('Logger.Mail.Port')))
            ->setTimeout(10)
            ->setAuthMode('login')
            ->setUsername(Config::get('Logger.Mail.User'))
            ->setPassword(Config::get('Logger.Mail.Password'));

        $mailer = new Swift_Mailer($transport);

        $messageContent = "In your API has occured error. Details are listed below.<br /><br />";
        $messageContent .= '<pre style="border-left: palevioletred 5px solid; font-family: Consolas, \'Lucida Console\', Monaco, monospace; font-size: 12px; padding-left: 5px;">' . $message . '</pre>';

        $message = (new Swift_Message('API ERROR ALERT'))
            ->setContentType('text/html')
            ->setFrom(Config::get('Logger.Mail.User'))
            ->setTo([Config::get('Logger.Mail.RecipientAddress')])
            ->setBody($messageContent);

        return (bool)$mailer->send($message);
    }

}
