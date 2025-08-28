<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-06 22:44:30
 * @modify date 2023-06-04 06:20:36
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

use Exception;
use PHPMailer\PHPMailer\{SMTP,PHPMailer};
use SLiMS\Mail\Queue;
use SLiMS\Mail\TemplateContract;

class Mail extends PHPMailer
{
    use Queue;
    
    private static $instance = null;
    public static $mode = 'singleton';

    /**
     * Initialize default php mailer
     *
     * @return void
     */
    private function __construct()
    {
        parent::__construct(...func_get_args());
        $mail = config('mail');
        
        //Server settings
        $this->SMTPDebug = $mail['debug'];
        $this->isSMTP();
        $this->Host = $mail['server'];
        $this->SMTPAuth = $mail['enable'];
        $this->Username = $mail['auth_username'];
        $this->Password = $mail['auth_password'];

        // SMTP secure option
        if ($mail['SMTPSecure'] === 'tls') {
            $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else if ($mail['SMTPSecure'] === 'ssl') {
            $this->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
        $this->Port = $mail['server_port'];
        
        //Recipients
        $this->setFrom($mail['from'], $mail['from_name']);
        $this->addReplyTo($mail['reply_to'], $mail['reply_to_name']);
        $this->charSet($mail['charset']??'UTF-8');
    }

    public static function getInstance()
    {
        if (!empty(self::$mode) && self::$mode != 'singleton') self::$instance = null;
        if (is_null(self::$instance)) self::$instance = new Mail(true);
        return self::$instance;
    }

    /**
     * Set destionation mail address
     *
     * @param string $mailAddress
     * @param string $mailName
     * @return Mail
     */
    public static function to(string $mailAddress, string $mailName = 'Member')
    {
        self::getInstance()->addAddress($mailAddress, $mailName);
        return self::getInstance();
    }

    /**
     * Mail subject
     *
     * @param string $mailObject
     * @return Mail
     */
    public function subject(string $mailObject)
    {
        $this->Subject = $mailObject;
        return $this;
    }

    /**
     * Set content charset
     *
     * @param string $charSet
     * @return void
     */
    public function charSet(string $charSet)
    {
        $this->CharSet = $charSet;
    }

    /**
     * Set plain message without html format
     *
     * @param string $mailPlainMessage
     * @return Mail
     */
    public function message(string $mailPlainMessage)
    {
        $this->isHTML(false);
        $this->Body = $mailPlainMessage;
        $this->AltBody = $mailPlainMessage;
        
        return $this;
    }

    /**
     * Send an email with formated template
     *
     * @param string $templateClass
     * @return Mail
     */
    public function loadTemplate(object $template)
    {
        if (!$template instanceof TemplateContract) throw new Exception("Class {$template} is not instance of SLiMS\Mail\TemplateContract!");

        $this->isHTML(true); // html yes!
        $this->msgHTML($template->render());
        if (is_object($template->render())) $this->AltBody = $template->render()->asAltBody();
        
        return $this;
    }

    /**
     * Attach some file
     *
     * @param string $filePath
     * @param array $options
     * @return Mail
     */
    public function attachment(string $filePath, array $options = [])
    {
        $this->addAttachment($filePath, ...$options);
        return $this;
    }

    /**
     * An option to override environment
     * setting
     *
     * @param string $envName
     * @return Mail
     */
    public function setEnv(string $envName)
    {
        // bypass if env not available!
        if (!array_key_exists($envName, self::availableEnv())) return $this;

        // override debug status
        $this->SMTPDebug = self::availableEnv()[$envName][0];
        return $this;
    }

    /**
     * PHPMailer environment list
     *
     * @return array
     */
    public static function availableEnv()
    {
        return [
            'Production' => [SMTP::DEBUG_OFF, __('Production')],
            'Development' => [SMTP::DEBUG_SERVER, __('Development')]
        ];
    }
}