<?php
namespace Jiny\Members;

/**
 * 회원가입 처리
 */
class Registration
{
    private $Auth;
    private $db;
    private $conf;
    public function __construct()
    {
        $dbinfo = \jiny\dbinfo();
        $this->db = \jiny\mysql($dbinfo);
        $this->Auth = new \Jiny\Members\Auth($this);
        $this->conf = \jiny\json_get_object("../Config/Login.json");
    }

    public function main($params=null)
    {
        if($this->Auth->status()) {
            // echo "로그아웃 상태에서만 회원가입이 가능합니다.";
            // post redirect get pattern
            header("HTTP/1.1 301 Moved Permanently");
            header("location:".$this->conf->mypage->uri); 
        } else {
            // 메서드 콜백호출
            $http = \jiny\http();
            return $http->callback($this);
        }
    }

    /**
     * 회원가입동의 확인검증
     */
    private function isAgreeRequire()
    {
        return isset($this->conf->regist->agree->require) ? $this->conf->regist->agree->require : false;
    }
    private function isAgreeFields()
    {
        return isset($this->conf->regist->agree->fields) ? $this->conf->regist->agree->fields : [];
    }
    private function agree()
    {
        if ($this->isAgreeRequire()) {
            $i = 0;
            $fields = $this->isAgreeFields();
            foreach ( $fields as $name) {
                if(isset($_POST['data'][$name])) $i++;
            }
            if($i == count($fields)) return true; else return false;
        } else {
            return true;
        }
    }


    public function GET()
    {
        // GET 동작
        if (!$this->isAgreeRequire()) {
            // echo "GET 회원가입 입력<br>";
            return $this->registForm();
        } else {
            // echo "회원동의가 필요합니다. /agree 이동...<br>";
            // post redirect get pattern
            header("HTTP/1.1 301 Moved Permanently");
            header("location:".$this->conf->regist->agree->uri); 
        }      
    }


    public function POST()
    {
        // echo "POST 동작<br>";        
        if ($this->agree()) {
            if($_POST['mode']=="newup") {
                // 회원가입절차
                return $this->registration();
            } else {
                // 회원가입폼 출력
                return $this->registForm();
            }            
        } else {
            // echo "회원동의가 필요합니다.<br>";
            // post redirect get pattern
            header("HTTP/1.1 301 Moved Permanently");
            header("location:".$this->conf->regist->agree->uri); 
        }      
    }


    private function registForm($args=[])
    {
        $args=['error_message'=>$this->error_message];

        $file = $this->conf->regist->forms->resource;
        $body = \jiny\html_get_contents($file, $args);

        foreach ($this->conf->regist->agree->fields as $key) {
            if (isset($_POST['data'][$key])) 
            $body = str_replace("</form>","<input type='hidden' name='data[".$key."]' value='on'></form>",$body);
        }
        return $body;
    }

    /**
     * 유효성 체크
     */
    private $error_message = "";
    private function validate($objs, $data)
    {
        foreach ($objs as $obj) {
            if ($obj->require) {
                $key = $obj->name;
                // echo $key."<br>";
                if( !isset($data[$key]) || empty($data[$key]) ) {
                    $this->error_message = $obj->message;
                    //echo $obj->message;
                    return false;
                } else {
                    $type = $obj->type;
                    //echo $data[$key];
                    if($type == "email" && filter_var($data[$key], FILTER_VALIDATE_EMAIL) === false) {
                        $this->error_message = $data[$key]." 유효하지 않는 이메일 타입입니다.";
                        return false;
                    } else 
                    if($type == "password" && filter_var($data[$key], FILTER_SANITIZE_STRING) === false) {
                        $this->error_message = "패스워드로 적합하지 않는 문자가 섞여 있습니다.";
                        return false;
                    }
                }
            }
        }
        return true;
    }
    private function isValidate()
    {
        return isset($this->conf->regist->forms->validate) ? $this->conf->regist->forms->validate : [];
    }

    private function registration()
    {
        // 회원가입 처리
        //echo "회원가입처리<br>";
        $data =\jiny\formData();

        $validate = $this->isValidate();
        if(!empty($validate)) {
            //echo "유효성 체크";
            $_validate = $this->validate($validate, $data);
        } else {
            // 유효성 체크 없음.
            $_validate = true;
        }
        
        if($_validate) {
            // echo "<br>유효성 성공";
            if ($this->isUser($data['email'])) {
                $this->error_message = "가입할 수 없습니다. 이미 존재하는 이메일 입니다.";
                return $this->registForm();
            }
            else {
                $data['token'] = \hash("sha256", $data['email'].date("Y-m-d H:i:s"));
                if($id=$this->newInsert($data)) {
                    // 인증메일 코드 발송
                    if ($this->authMail($data)) {
                        $file = "../resource/members/registration_mail.html";
                        $body = \jiny\html_get_contents($file);
                        return $body;
                    }

                    exit;
                    // 테이터 삽입 성공
                    // post redirect get pattern
                    header("HTTP/1.1 301 Moved Permanently");
                    header("location:".$this->conf->mypage->uri);
                }
            }

        } else {
            // echo "<br>유효성 검증 실패<br>";
            return $this->registForm();
        }
    }

    private function authMail($data)
    {
       

            $link = "http://localhost:8000/login/confirm?token=".$data['token'];
            $mailbody = "첨부한 링크를 클릭하여 회원가입을 ";
            $mailbody .= "<a href='$link'>활성화</a>를 해주세요.";

            /// 

            // 메일발송
            // Create the Transport
            $smtp = new \Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl');
            $smtp->setUsername('lin2m200128@gmail.com');
            $smtp->setPassword('Hojin@3106');

            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($smtp);

            // Create a message
            $title = '[진달래꽃] 회원가입 인증';
            $message = new \Swift_Message($title);
            $message->setFrom(['infohojin@gmail.com' => 'HojinLee']);
            $message->setTo([$data['email'] ]);
            // $message->setBody($mailbody); // 일반텍스트
            $message->setBody($mailbody, 'text/html');
            
            // Send the message
            $result = $mailer->send($message);

            echo "인증메일이 발송되었습니다.";
            return $result;
        
    }

    private function isUser($email)
    {
        $MemDB = new \Jiny\Members\Database;
        if ($info = $MemDB->byEmail($email)) {
            return true;
        } else {
            return false;
        }
    }

    private function newInsert($data)
    {
        // 패스워드 암호화
        $PassWord = new \Jiny\Members\Encryption();
        $data['password'] = $PassWord->encryption($data['password']);

        // 데이터베이스 삽입
        $insert = $this->db->insert("members", $data)->autoField();
        if ($id = $insert->save()) {
            // echo "데이터 삽입 성공 = ".$id;
            return $id;
        } else {
            // echo "데이터 삽입 실패";
            return false;
        }
    }

}