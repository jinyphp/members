<?php

namespace Jiny\Members;
/**
 * 패스워드 찾기
 */
class Password extends Config
{
    private $conf;
    private $_db;
    private $error_message = "";
    public function __construct()
    {
        // echo __CLASS__;
        $this->conf = \jiny\json_get_object("../Config/Login.json");

        $dbinfo = \jiny\dbinfo();
        $this->_db = \jiny\mysql($dbinfo);
    }

    public function main()
    {
        $http = \jiny\http();
        return $http->callback($this);
    }

    public function GET()
    {
        // GET 동작
        if (isset($_GET['token'])) {
            // 3.
            $tablename = "password_reset";
            $select = $this->_db->select($tablename, ['id','email','token'])->where(["token"]);
            $query = $select->build()->getQuery();
            //echo $query;
                // echo $this->Auth->email()."<br>";
                //->autoField()
            $row = $select->autoTable()->runAssoc(['token'=>$_GET['token']]);
            if ($row) {
                //echo "새로운 패스워드를 입력하세요";
                $file = "../resource/members/password_reset.html";
                $vars=[
                    'token'=>$_GET['token'],
                    'email'=>$row['email']
                ];
                $body = \jiny\html_get_contents($file,$vars);
                return $body;
            } else {
                echo "토큰 불일치";
            }


            
        } else {
            // 1.
            return $this->view(); 
        }        
    }

    private function view()
    {
        $vars = [
            'error_message'=>$this->error_message
        ];

        $file = $this->conf->password->resource;
        $body = \jiny\html_get_contents($file,$vars);
        return $body;
    }

    public function POST()
    {
        if($_POST['mode'] == "reset") {
            // echo "패스워드 갱신";
            // print_r($_POST);
            // 4.
            if ($_POST['data']['password1'] == $_POST['data']['password2']) {

                $tablename = "password_reset";
                $select = $this->_db->select($tablename, ['id','email','token'])->where(["token"]);
                $query = $select->build()->getQuery();
                //echo $query;
                $row = $select->runAssoc(['token'=>$_POST['token']]);
                //print_r($row);
                if ($row) {
                    //echo "패스워드 암호화 = ";
                    $passwd = $_POST['data']['password1'];
                    // 패스워드 암호화
                    $PassWord = new \Jiny\Members\Encryption();
                    $passwd = $PassWord->encryption($passwd);
                    //echo $passwd;

                    // 패스워드 갱신
                    //echo "패스워드 갱신 <br>";
                    $MemDB = new \Jiny\Members\Database;
                    $MemDB->updateByEmail($row['email'], ['password'=>$passwd]);
                }

                // 패스워드 요청기록 삭제
                //echo "기록 삭제 <br>";
                $this->_db->delete($tablename)->id($row['id']);

                // post redirect get pattern
                header("HTTP/1.1 301 Moved Permanently");
                header("location:"."/login");

            } else {
                echo "패스워드와 확인이 일치하지 않습니다.";
            }
        } else {
            // 2.
            if (empty($_POST['data']['email'])) {
                $this->error_message = "유효하지 않는 이메일 입니다.";
                return $this->view(); 

            } else {
                $email = $_POST['data']['email'];
                $tablename = "password_reset";
                $select = $this->_db->select($tablename, ['id','email','updated_at'])->where(["email"]);
                $query = $select->build()->getQuery();
                //echo $query;
                // echo $this->Auth->email()."<br>";
                //->autoField()
                $row = $select->autoTable()->runAssoc(['email'=>$email]);

                if ($row) {
                    //print_r($row);
                    // 이전에 패스워드 재요청 존재
                    $data = $_POST['data'];
                    $data['token'] = \hash("sha256", $email.$row['updated_at']);

                    $update = $this->_db->update($tablename, $data)->id($row['id']);


                } else {
                    // 신규요청
                    // 데이터베이스 삽입
                    $data = $_POST['data'];
                    $data['token'] = \hash("sha256", $email);
                    $insert = $this->_db->insert($tablename, $data)->autoField();
                    if ($id = $insert->save()) {
                        //echo "데이터 삽입 성공 = ".$id;
                        // return $id;
                    } else {
                        //echo "데이터 삽입 실패";
                        // return false;
                    }
                }

                $link = "http://localhost:8000/login/password?token=".$data['token'];
                $mailbody = "첨부한 링크를 클릭하여";
                $mailbody .= "<a href='$link'> 비밀번호 변경</a>을 하세요";

                // 메일발송
                // Create the Transport
                $smtp = new \Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl');
                $smtp->setUsername('lin2m200128@gmail.com');
                $smtp->setPassword('Hojin@3106');

                // Create the Mailer using your created Transport
                $mailer = new \Swift_Mailer($smtp);

                // Create a message
                $title = '[진달래꽃] 회원메일 초기화 링크';
                $message = new \Swift_Message($title);
                $message->setFrom(['infohojin@gmail.com' => 'HojinLee']);
                $message->setTo([$email]);
                // $message->setBody($mailbody); // 일반텍스트
                $message->setBody($mailbody, 'text/html');
                


                // Send the message
                $result = $mailer->send($message);
            
                //
        
                $file = "../resource/members/password_mail.html";
                $body = \jiny\html_get_contents($file);

               
                return $body;


            }
        }        
    }

}
