# jinyPHP Members
회원관리 페키지

## 로그아웃
logout 클래스는 접속된 회원의 인증을 아웃합니다.

### 로그아웃 링크걸기
라우트 정보를 통하여 logout 컨트롤러 클래스를 연결할 수 있습니다.

```json
{
    "uri":"/login",
    "type":"form",
    "auth":false,
    "enable":true,
    "controller":{
        "name":"\\Jiny\\Members\\Logout",
        "method":"main"
    },
    "permit":{
        
    },
    "description":"로그인폼"
}
```


### 설정정보

```json
```

로그아웃 리소스 정보가 없는 경우,
login 페이지로 이동합니다.

