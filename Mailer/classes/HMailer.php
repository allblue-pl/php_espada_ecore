<?php namespace EC\Mailer;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HMailer extends E\Module {

	static public function NewMail($to_mail, $to_name)
	{
		$config = new EC\CConfig('Mailer');

		$mail = new CMail($config->from_Mail, $config->from_Name,
				$to_mail, $to_name);

		if ($config->smtp_Use) {
			$mail->setSmtp(
				$config->smtp_Host,
				$config->smtp_Auth,
				$config->smtp_Username,
				$config->smtp_Password,
				$config->smtp_Secure,
				$config->smtp_Port
			);
		}

		return $mail;
	}

}
