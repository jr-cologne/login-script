<?php

namespace LoginScript\Mail;

class Mail {

  protected $to;
  protected $from;
  protected $subject;
  protected $message;
  protected $headers;
  protected $sent = false;

  public function __construct(string $to, string $from, string $subject, string $message, array $headers = []) {
    $this->to = $to;
    $this->from = $from;
    $this->subject = $this->getSubject($subject);
    $this->message = $message;

    if ($headers) {
      $this->headers = $this->getHeaders($headers);
    } else {
      $this->headers = $this->getHeaders();
    }
    
    $this->sent = $this->sendMail();
  }

  public function sent() {
    return $this->sent;
  }

  protected function getSubject(string $subject) {
    return '=?UTF-8?B?' . base64_encode($subject) . '?=';
  }

  protected function getHeaders(array $custom_headers = []) {
    if ($custom_headers) {
      return implode("\r\n", $custom_headers);
    }

    return implode("\r\n", [
      'MIME-Version: 1.0',
      'Content-type: text/plain; charset=utf-8',
      "From: { $this->from }",
      "Reply-To: { $this->from }",
      "Subject: { $this->subject }"
    ]);
  }

  protected function sendMail() {
    return mail($this->to, $this->subject, $this->message, $this->headers);
  }

}
