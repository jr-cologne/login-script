<?php

namespace LoginScript\Mail;

class Message {

  protected $message;

  public function __construct(string $text, array $replacements = []) {
    $this->message = $this->createMessage($text, $replacements);
  }

  public function getMessage() : string {
    return $this->message;
  }

  protected function createMessage(string $text, array $replacements) : string {
    foreach ($replacements as $placeholder => $replacement) {
      unset($replacements[$placeholder]);

      $replacements[':' . $placeholder] = $replacement;
    }

    return str_replace(array_keys($replacements), $replacements, $text);
  }

}
