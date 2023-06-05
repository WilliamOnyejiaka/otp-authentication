<?php
declare(strict_types=1);
namespace Module;

ini_set('display_errors', true);

require_once __DIR__ . "/../vendor/autoload.php";

class Wcrypt
{
    private string $secretKey;
    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }
    
    public function encrypt(string $data)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->secretKey, 0, $iv);
        return base64_encode($encrypted . "::" . $iv);
    }
    
    public function decrypt($encryptedData)
    {
        list($encrypted, $iv) = explode("::", base64_decode($encryptedData), 2);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $this->secretKey, 0, $iv);
    }
}
