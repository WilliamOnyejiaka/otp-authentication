<?php
declare(strict_types=1);
namespace Module;

ini_set('display_errors', true);

require_once __DIR__ . "/../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{

    private string $username = 'williamonyejiaka08062528003@gmail.com';
    private string $password = 'william44616';
    private string $tls = 'tls';
    private int $port = 587;
    private string $sender = 'williamonyejiaka08062528003@gmail.com';
    private string $senderName = '';
    private string $subject = '';
    private string $body = '';
    private string $receiver = '';
    private string $receiverName = '';
    private PHPMailer $mail;
    private string $host = 'smtp-mail.outlook.com';

    public function __construct(string $receiver, $receiverName, string $subject, string $body, string $senderName = "noreply")
    {
        $this->receiver = $receiver;
        $this->receiverName = $receiverName;
        $this->subject = $subject;
        $this->body = $body;
        $this->senderName = $senderName;
        $this->mail = new PHPMailer();
    }

    public function send_mail()
    {
        $this->mail->isSMTP();
        $this->mail->Host = $this->host;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->username;
        $this->mail->Password = $this->password;
        $this->mail->SMTPSecure = $this->tls;
        $this->mail->Port = 587;

        $this->mail->setFrom($this->sender, $this->senderName);
        $this->mail->addAddress($this->receiver, $this->receiverName);
        $this->mail->Subject = $this->subject;
        $this->mail->Body = $this->body;

        
        if ($this->mail->send()) {
            return true;
        } else {
            print_r($this->mail->ErrorInfo);
            return false;
        }

    }
}

// function convert_to_seconds(int $time, string $unit)
// {
//     if ($unit == "minutes" || $unit == "m" || $unit == "min") {
//         return $time * 60;
//     } elseif ($unit == "hours" || $unit == "h" || $unit == "hr") {
//         return $time * 3600;
//     } elseif ($unit == "days" || $unit == "day" || $unit == "d") {
//         return $time * 24 * 3600;
//     } else {
//         return 0;
//     }
// }



// $time = convert_to_seconds(5, "md");
// echo $time;