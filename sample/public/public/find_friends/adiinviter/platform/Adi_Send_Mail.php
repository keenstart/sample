<?php

use Zend\Mail;

class Adi_Send_Mail_Platform extends Adi_Send_Mail_Base
{
	public $send_multipart_email = true;
	public $use_quoted_printable = true;
	public $delimiter = "\r\n";
	public $charset = "UTF-8";

	public $use_zend_mailer = true;

	/**
	 * This function should send email
	 * @syntax
	 *     $this->receiver    : Receiver's email address.
	 *     $this->subject     : Subject of email.
	 *     $this->body        : Final email body.
	 *     $this->headers_arr : MIME headers array
	 *     $this->headers     : Final MIME headers string built by 
	 *                          combining $this->headers_arr with 
	 *                          the delimiter $this->delimiter.
	 * 
	 *     $this->plain_body  : Plain text body.
	 *                          If $this->send_multipart_email = true
	 * 
	 *     $this->html_body   : HTML content body.
	 */
	function send_mail()
	{
		if($this->use_zend_mailer === true)
		{
			$mail = new Mail\Message();
			$mail->setBody($this->body);
			$mail->setFrom($this->sender_email, $this->sender_name);
			$mail->setTo($this->receiver);
			$mail->setSubject($this->subject);

			$transport = new Mail\Transport\Sendmail();
			$transport->send($mail);
		}
		else
		{
			return parent::send_mail();
		}
	}
}


?>