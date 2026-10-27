<?php

declare(strict_types=1);

namespace Tyto\Http;

final class Response
{
    private array $headers = [];

    public function __construct(
        private string $content = '',
        private int $statusCode = 200,
        array $headers = []
    ) {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json(array $data, int $statusCode = 200): self 
    {
        $this->statusCode = $statusCode;
        $this->content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $this->setHeader('Content-Type', 'application/json; charset=UTF-8');
        return $this;
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    public function send(): void
    {
        if(!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header('{$name}: {$value}');
            }
        }

        echo $this->content;
    }
}