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
    /*
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
    */

    

    

    



}