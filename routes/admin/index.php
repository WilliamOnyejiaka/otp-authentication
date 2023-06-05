<?php
declare(strict_types=1);
ini_set("display_errors", 1);

require_once __DIR__ . "/../../vendor/autoload.php";
include_once __DIR__ . "/../../config/config.php";

use Lib\Router;
use Lib\Controller;
use Lib\Validator;
use Lib\Serializer;
use Module\Mailer;
use Model\Authentication;
use Model\User;
use Module\Wcrypt;



$admin = new Router("admin", true);
$controller = new Controller();

$admin->get("/create-authentication-table", fn() => $controller->public_controller(function ($body, $response) {

    $authentication = new Authentication();

    ($authentication->table_exists()) && $response->send_response(400, [
        'error' => true,
        'message' => "table exists already",
    ]);

    ($authentication->create_tbl()) && $response->send_response(200, [
        'error' => false,
        'message' => "table has been created successfully",
    ]);

    $response->send_response(500, [
        'error' => true,
        'message' => "something went wrong",
    ]);
}));

$admin->get("/create-users-table", fn() => $controller->public_controller(function ($body, $response) {

    $user = new User();

    ($user->table_exists()) && $response->send_response(400, [
        'error' => true,
        'message' => "table exists already",
    ]);

    ($user->create_tbl()) && $response->send_response(200, [
        'error' => false,
        'message' => "table has been created successfully",
    ]);

    $response->send_response(500, [
        'error' => true,
        'message' => "something went wrong",
    ]);
}));

$admin->post("/sign-up", fn() => $controller->public_controller(function ($body, $response) {

    $validator = new Validator();
    $validator->validate_body($body, ['name', 'email', 'password']);
    [$name, $email, $password] = [$body->name, $body->email, $body->password];

    $validator->validate_email_with_response($email);
    $validator->validate_password_with_response($password, 5);
    $password = password_hash($password, PASSWORD_DEFAULT);

    $user = new User();
    $user_exist = (new Serializer(['email']))->tuple($user->get_user_with_email($email));

    if ($user_exist) {
        $response->send_response(400, [
            'error' => true,
            'message' => "email exists",
        ]);
    }

    if ($user->create_user($name, $email, $password)) {
        $response->send_response(200, [
            'error' => false,
            'message' => "user created successfully",
        ]);
    }

    $response->send_response(500, [
        'error' => true,
        'message' => "something went wrong",
    ]);
}));

$admin->get("/login", fn() => $controller->public_controller(function ($body, $response) {
    $email = $_SERVER['PHP_AUTH_USER'] ?? null;
    $password = $_SERVER['PHP_AUTH_PW'] ?? null;

    if (!$email || !$password) {
        $response->send_response(400, [
            'error' => true,
            'message' => "all values needed"
        ]);
    }

    $user = new User();
    $current_user = (new Serializer(['id','email', 'password']))->tuple($user->get_user_with_email($email));

    if ($current_user) {
        $valid_password = password_verify($password, $current_user['password']);
        if ($valid_password) {

            $otp = random_int(1000,9999);
            $otp_expiration_time = time() + 60;

            $authentication = new Authentication();
            $auth_exists = (new Serializer(['otp']))->tuple($authentication->get_auth_with_user_id($current_user['id']));

            if($auth_exists){
                // $mailer = new Mailer('williamonyejiaka08062528003@gmail.com', 'William Onyejiaka', 'OTP Auth', "Your otp is $otp,valid for 1 minute");

                if($authentication->update_auth_otp($current_user['id'],$otp,$otp_expiration_time)){
                    /*($mailer->send_mail()) && */ $response->send_response(200, [
                        'error' => false,
                        'message' => "otp has been sent"
                    ]);
                }
                $response->send_response(500, [
                    'error' => true,
                    'message' => "something went wrong",
                ]);
            }
            if($authentication->create_auth($current_user['id'], $otp, $otp_expiration_time)){

                /*($mailer->send_mail()) && */$response->send_response(200, [
                    'error' => false,
                    'message' => "otp has been sent"
                ]);

                $response->send_response(500, [
                    'error' => true,
                    'message' => "something went wrong",
                ]);
            }

            $response->send_response(500, [
                'error' => true,
                'message' => "something went wrong",
            ]);

            $response->send_response(400, [
                'error' => true,
                'message' => "invalid password"
            ]);
        }

        $response->send_response(400, [
            'error' => true,
            'message' => "invalid password"
        ]);
    }

    $response->send_response(404, [
        'error' => true,
        'message' => "email does not exist"
    ]);
}));

$admin->get("/confirm-authentication", fn() => $controller->public_controller(function ($body, $response) {

    $validator = new Validator();
    $validator->validate_body($body,['otp','email']);
    $otp = $body->otp;
    $email = $body->email;

    $user = new User();
    $current_user = (new Serializer(['id','email','name']))->tuple($user->get_user_with_email($email));

    if(isset($current_user['id'])){
        $authorization = new Authentication();

        $auth_data = (new Serializer(['id','otp', 'otp_expiration_time']))->tuple($authorization->get_auth_with_user_id($current_user['id']));

        if (isset($auth_data['otp']) && $auth_data['otp'] == $otp) {
            if ($auth_data['otp_expiration_time'] >= time()) {
                $wcrpyt = new Wcrypt(config('secret_key'));
                $token =$wcrpyt->encrypt(strval($auth_data['id']));
                $token_expiration_time = time() + 3600;


                if (
                    $authorization->update_auth_token(
                        $current_user['id'],
                        $token,
                        $token_expiration_time
                    )
                ) {
                    setcookie('token', $token, $token_expiration_time, '/');

                    $response->send_response(200, [
                        'error' => false,
                        'message' => "logged in",
                        'data' => [
                            'id' => $current_user['id'],
                            'name' => $current_user['name'],
                            'email' => $current_user['email']
                        ]
                    ]);
                }
                $response->send_response(500, [
                    'error' => true,
                    'message' => "something went wrong",
                ]);
            }
            $response->send_response(400, [
                'error' => true,
                'message' => "otp has expired"
            ]);
        }

        $response->send_response(400, [
            'error' => true,
            'message' => "invalid otp",
        ]);
    }

    $response->send_response(404, [
        'error' => true,
        'message' => "user not found"
    ]);
}));

$admin->get("/get-user",fn() => $controller->public_controller(function($body,$response){
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];
        $wcrpyt = new Wcrypt(config('secret_key'));
        $token_id = intval($wcrpyt->decrypt($token));

        $authentication = new Authentication();
        $active_auth = (new Serializer(['id','token','token_expiration_time','user_id']))->tuple($authentication->get_auth_with_id($token_id));
        if($active_auth){
            if($active_auth['token'] == $token && $active_auth['token_expiration_time'] >= time()){
                $user = new User();
                $current_user = (new Serializer(['id','name','email']))->tuple($user->get_user_with_id($active_auth['user_id']));
                $response->send_response(200, [
                    'error' => false,
                    'message' => $token,
                    'data' => $current_user
                ]);
            }
            $response->send_response(400, [
                'error' => true,
                'message' => "invalid token"
            ]);
            
        }
        $response->send_response(400, [
                'error' => true,
                'message' => "invalid token",
        ]);
    } else {
        $response->send_response(400, [
            'error' => true,
            'message' => "user not logged in"
        ]);
    }
}));


$admin->get("/log-out", fn() => $controller->public_controller(function ($body, $response) {
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];
        $wcrpyt = new Wcrypt(config('secret_key'));
        $token_id = intval($wcrpyt->decrypt($token));

        $authentication = new Authentication();
        if($authentication->delete_auth($token_id)){
            setcookie('token', '', time() - 3600, '/');
            $response->send_response(200, [
                'error' => true,
                'message' => "user has been logged out"
            ]);
        }
        $response->send_response(500, [
            'error' => true,
            'message' => "something went wrong"
        ]);
    } else {
        $response->send_response(400, [
            'error' => true,
            'message' => "user not logged in"
        ]);
    }
    
}));



$admin->get("/test", fn() => $controller->public_controller(function ($body, $response) {

    $data = "Hello, world!";
    $secretKey = "mySecretKey";

    $wcrpyt = new Wcrypt($secretKey);

    $encrypted = $wcrpyt->encrypt($data);
    $decrypted = $wcrpyt->decrypt($encrypted);

    $response->send_response(500, [
        'error' => $encrypted,
        'message' => $decrypted,
    ]);
}));


$admin->run();