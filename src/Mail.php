<?php

namespace Jiny\Members;

class Mail
{
    private $mailer;
    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        // Create the Transport
        $smtp = new \Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl');
        $smtp->setUsername('lin2m200128@gmail.com');
        $smtp->setPassword('Hojin@3106');

        // Create the Mailer using your created Transport
        $this->mailer = new \Swift_Mailer($smtp);
    }

    public function send($data)
    {
        // 인증메일 코드 발송
        return $this->mail($data);
    }

    private function mailBody($data)
    {
        $link = "http://localhost:8000/login/confirm?token=".$data['token'];
        
        $mailbody = "첨부한 링크를 클릭하여 회원가입을 ";
        $mailbody .= "<a href='$link'>활성화</a>를 해주세요.";
        return $mailbody;
    }

    private function mail($data)
    {
        // 인증링크 메일Body
        $mailbody = $this->mailBody($data);

        $title = '[jiny-workvoard] 회원가입을 인증해 주세요';
        $message = new \Swift_Message($title);
        $message->setFrom(['infohojin@gmail.com' => 'HojinLee']);
        $message->setTo([$data['email'] ]);
        
        // $message->setBody($mailbody); // 일반텍스트
        $message->setBody($mailbody, 'text/html');
            
        // Send the message
        $result = $this->mailer->send($message);

        //echo "인증메일이 발송되었습니다.";
        return $result;
    }

}