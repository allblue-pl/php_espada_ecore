<?php namespace EC\Mailer;
defined('_ESPADA') or die(NO_ACCESS);

require(__DIR__.'/../3rdparty/PHPMailer/src/Exception.php');
require(__DIR__.'/../3rdparty/PHPMailer/src/OAuth.php');
require(__DIR__.'/../3rdparty/PHPMailer/src/PHPMailer.php');
require(__DIR__.'/../3rdparty/PHPMailer/src/POP3.php');
require(__DIR__.'/../3rdparty/PHPMailer/src/SMTP.php');

use E, EC,
	PHPMailer\PHPMailer;

class CMail
{

	private $from_Mail = '';
	private $from_Name = '';

    private $tos = null;
    private $replyTos = [];
	private $ccs = [];
	private $bcce = [];

    private $attachments = [];
    private $images = [];

	private $mail = null;

	private $subject = '';
	private $text = '';
	private $html = '';

	private $error = null;

	public function __construct($from_mail, $from_name,
			$to_mail, $to_name = '')
	{
		$this->from_Mail = $from_mail;
		$this->from_Name = $from_name === '' ? $from_mail : $from_name;

		$this->addTo($to_mail, $to_name);

		$this->mail = new PHPMailer\PHPMailer(true);
		$this->mail->isHTML(true);
		$this->mail->CharSet = 'UTF-8';
	}

	public function setSmtp($host, $auth, $username, $password, $secure, $port)
	{
		$this->mail->isSMTP();
		$this->mail->Host = $host;
		$this->mail->SMTPAuth = $auth;
		$this->mail->Username = $username;
		$this->mail->Password = $password;
		$this->mail->SMTPSecure = $secure;
		$this->mail->Port = $port;

		$this->mail->SMTPOptions = [
			'ssl' => [
				'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
			]
		];
	}

	public function setSubject($subject)
	{
		$this->subject = $subject;
	}

	public function addAttachment($file_path, $file_name = '')
	{
		if ($file_name === '')
			$file_name = basename($file_path);

		$this->attachments[] = array($file_path, $file_name);
    }
    
    public function addImage($filePath, $fileName = null)
    {
        if ($fileName === null)
            $fileName = basename($filePath);
            
        $this->images[] = [ $filePath, $fileName ];
    }

	public function addTo($mail, $name = null)
	{
		if ($name === null)
			$name = $mail;

		$this->tos[] = array($mail, $name);
		// $this->mail->addAddress($mail, $name);
    }
    
    public function setReplyTo($mail, $name = null)
    {
        if ($name === null)
			$name = $mail;

        $this->replyTos = [];
        $this->replyTos[] = [ $mail, $name ];
    }

	public function setTo($mail, $name = '')
	{
		$this->tos = [];
		$this->addTo($mail, $name);
	}

	// public function addCc($mail, $name)
	// {
	// 	if ($name === null)
	// 		$name = $mail;
	//
	// 	$this->ccs[] = array($mail, $name);
	// 	// $this->mail->addCC($mail, $name);
	// }
	//
	// public function addBcc($mail, $name)
	// {
	// 	if ($name === null)
	// 		$name = $mail;
	//
	// 	$this->bccs[] = array($mail, $name);
	// 	//$this->mail->addBCC($mail, $name);
	// }

	public function setText($text)
	{
		$this->text = $text;
	}

	public function setHtml($html)
	{
		$this->html = $html;
	}

	public function send()
	{
		try {
			if (E\Config::IsType('no-mails'))
				return true;

			$this->mail->Subject = $this->subject;
			$this->mail->From = $this->from_Mail;
			$this->mail->FromName = $this->from_Name === '' ?
				$this->from_Mail : $this->from_Name;

			foreach ($this->tos as $to)
                $this->mail->addAddress($to[0], $to[1]);
                
            foreach ($this->replyTos as $replyTo)
                $this->mail->addReplyTo($replyTo[0], $replyTo[1]);

			$text = $this->text;
			$html = $this->html;

			if ($html === '')
				$html = str_replace("\n", '<br />', $text);

			$this->mail->Body = $html;
			$this->mail->AltBody = $text;

			foreach ($this->attachments as $file_path)
                $this->mail->addAttachment($file_path[0], $file_path[1]);
                
            foreach ($this->images as $imageInfo)
				$this->mail->addEmbeddedImage($imageInfo[0], $imageInfo[1]);

			if (!$this->mail->send()) {
				$this->error = $this->mail->ErrorInfo;
				return false;
			}

			return true;
		} catch (\phpmailerException $e) {
			$this->error = $e->errorMessage();
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
		}

		return false;
	}

	public function getError()
	{
		return $this->error;
	}

}
