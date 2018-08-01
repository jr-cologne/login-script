<?php

namespace LoginScript\Mail;

use LoginScript\Config\Config;

use \Swift_SmtpTransport;
use \Swift_Mailer;
use \Swift_Message;

class Mail {

  protected $client;

  protected $message;

  protected $sent = false;

  public function __construct(string $from, string $to, string $subject, string $message) {
    $this->initMailClient(Config::get('mail/smtp_config_file'));

    $this->message = $this->getMessage(compact('from', 'to', 'subject', 'message'));

    $this->sent = $this->sendMail();
  }

  public function sent() : bool {
    return $this->sent;
  }

  protected function initMailClient(string $config) {
    if (is_string($config) && !file_exists($config)) {
      $config = json_decode($config, true);
    } else {
      $config = json_decode(file_get_contents($config, true), true);
    }

    $smtp_server = $config['smtp_server'] ?? null;
    $smtp_port = $config['smtp_port'] ?? null;
    $smtp_encryption = $config['smtp_encryption'] ?? null;
    $smtp_username = $config['smtp_username'] ?? null;
    $smtp_password = $config['smtp_password'] ?? null;

    if ( !$smtp_server || !$smtp_port || !$smtp_encryption || !$smtp_username || !$smtp_password ) {
      throw new MailException('Invalid/Missing mail configs');
    }

    $transport = (new Swift_SmtpTransport($smtp_server, $smtp_port))
      ->setEncryption($smtp_encryption)
      ->setUsername($smtp_username)
      ->setPassword($smtp_password);

    $this->client = new Swift_Mailer($transport);
  }

  protected function getMessage(array $settings) {
    $from = $settings['from'] ?? null;
    $to = $settings['to'] ?? null;
    $subject = $settings['subject'] ?? null;
    $body = $settings['message'] ?? null;

    if ( !$from || !$to || !$subject || !$body ) {
      throw new MailClientException('Invalid/Missing message settings');
    }

    return (new Swift_Message($subject))
      ->setFrom($from)
      ->setTo($to)
      ->setBody($body);
  }

  protected function sendMail() : bool {
    if ( $this->message && $this->client->send($this->message) === 1 ) {
      return true;
    }

    return false;
  }

}
