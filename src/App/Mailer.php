<?php
namespace App;

class Mailer {

    private $from;
    private $headers = [];

    public function __construct($from, $headers = []) {
        $this->headers["Content-Type"] = "text/html; charset=UTF-8";
        $this->headers = array_merge($this->headers, $headers);
        $this->headers['From'] = $from;
    }

    public function send($to, $subject, $body, $headers = []) {
        $headers = array_merge($this->headers, $headers);
        $headersString = join("\r\n", array_map(function ($key, $value) {
            return sprintf("%s: %s", $key, $value);
        }, array_keys($headers), $headers));

        return mail($to, $subject, $body, $headersString);
    }
}
