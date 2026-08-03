<?php namespace EC\Mailer;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;
use EC\Config\CConfig;

class HMailer {
	static public function NewMail(string $toMail, string $toName) {
		$config = new CConfig('Mailer');

		$mail = new CMail($config->getR("from_Mail"), $config->getR("from_Name"),
				$toMail, $toName);

		if ($config->getR("smtp_Use")) {
			$mail->setSmtp(
				$config->getR("smtp_Host"),
				$config->getR("smtp_Auth"),
				$config->getR("smtp_Username"),
				$config->getR("smtp_Password"),
				$config->getR("smtp_Secure"),
				$config->getR("smtp_Port")
			);
		}

		return $mail;
	}
}
