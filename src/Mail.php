<?php

namespace Jiny\Members;

class Mail
{
    public function __construct()
    {
        // Create the Transport
        $smtp = new Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl');
        $smtp->setUsername('lin2m200128@gmail.com');
        $smtp->setPassword('Hojin@3106');

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($smtp);

        // Create a message
        $title = 'jinyphp mail test3';
        $message = new Swift_Message($title);
        $message->setFrom(['infohojin@gmail.com' => 'HojinLee']);
        $message->setTo(['infohojin@naver.com']);
        $message->setBody('this is test mail');


        // Send the message
        $result = $mailer->send($message);
    }
}