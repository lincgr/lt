<?php
include("PHPMailerAutoload.php");

class Mailer {

    private $_params;
    private $_errors;

    public function __construct() {
        $this->_params = $this->LoadParams();
        $this->_errors = array();
    }

    public function run() {
        if ($this->Validate()) {
            $res = $this->SendEmail();
            if ($res == true)
                $this->OnSuccess();
            else
                $this->OnError();
        } else
            $this->OnError();
    }

    private function LoadParams() {
        return $_POST['contact'];
    }

    private function Validate() {
        if (!(isset($this->_params['name']) && ($this->_params['name'] != '')))
            $this->_errors['name'] = 'empty_name';
        if (!(isset($this->_params['email']) && $this->_params['email'] != ''))
            $this->_errors['email'] = 'empty_email';
        else {
            $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
            if (!preg_match($email_exp, $this->_params['email']))
                $this->_errors['email'] = 'invalid';
        }
        if (!(isset($this->_params['subject']) && $this->_params['subject'] != ''))
            $this->_errors['subject'] = 'empty_subject';
        if (!(isset($this->_params['message']) && $this->_params['message'] != ''))
            $this->_errors['message'] = 'empty_message';

        return (count($this->_errors) == 0);
    }

    private function SendEmail() {
        //Create a new PHPMailer instance
        $mail = new PHPMailer;
        //Set who the message is to be sent from
        $mail->setFrom($this->_params['email'], $this->_params['name']);
        //Set an alternative reply-to address
        $mail->addReplyTo($this->_params['email'], $this->_params['name']);
        //Set who the message is to be sent to
        $mail->addAddress('lincgr@gmail.com', 'LatamLaw');
        //Set the subject line
        $mail->Subject = $this->_params['subject'];
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //Replace the plain text body with one created manually
        $mail->msgHTML($this->_params['message']);

        //send the message, check for errors
        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }

    private function OnSuccess() {
        echo '{"success": true}';
    }

    private function OnError() {
        $response = '{';
        $response .= '"success": false, "errors": [';

        foreach ($this->_errors as $key => $value) {
            $response .= "{ \"field\": \"$key\", \"error\": \"$value\"},";
        }
        if (count($this->_errors) > 0)
            $response = substr($response, 0, -1);
        $response .= ']}';

        echo $response;
    }

}

$mailer = new Mailer();
$mailer->run();
?>