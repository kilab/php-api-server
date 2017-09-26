<?php

namespace Kilab\Api;

use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

class Logger
{

    /**
     * Save given string to file.
     *
     * @param string $content
     *
     * @throws \Swift_SwiftException
     */
    public static function log(string $content): void
    {
        $filePath = BASE_DIR . 'logs/' . date('Y-m-d') . '-' . API_VERSION . '.log';

        if (!file_exists($filePath)) {
            touch($filePath);
            chmod($filePath, 0777);
        }

        $content = '[' . date('H:i:s') . ']: ' . $content;

        file_put_contents($filePath, $content . PHP_EOL, FILE_APPEND | LOCK_EX);

        if (Config::get('Logger.Mail.Enabled')) {
            $transport = (new Swift_SmtpTransport(Config::get('Logger.Mail.Host'), Config::get('Logger.Mail.Port')))
                ->setTimeout(10)
                ->setAuthMode('login')
                ->setUsername(Config::get('Logger.Mail.User'))
                ->setPassword(Config::get('Logger.Mail.Password'));

            $mailer = new Swift_Mailer($transport);

            $messageContent = "In your API has occured error. Details are listed below.<br /><br />";
            $messageContent .= '<pre style="border-left: palevioletred 5px solid; font-family: Consolas, \'Lucida Console\', Monaco, monospace; font-size: 12px; padding-left: 5px;">' . $content . '</pre>';

            $message = (new Swift_Message('API ERROR'))
                ->setContentType('text/html')
                ->setFrom(Config::get('Logger.Mail.User'))
                ->setTo([Config::get('Logger.Mail.RecipientAddress')])
                ->setBody($messageContent);

            $mailer->send($message);
        }
    }
}
