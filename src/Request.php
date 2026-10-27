<?php
// vendor/tyto-php/http/src/Request.php
declare(strict_types=1);

namespace Tyto\Http;

final class Request
{
    private ?array $jsonBody = null;

    public function __construct(
        private readonly array $server,
        private readonly array $get,
        private readonly array $post,
        private readonly array $files,
        private readonly array $cookie,
    ) {}

    public static function createFromGlobals(): self
    {
        return new self(
            $_SERVER,
            $_GET,
            $_POST,
            $_FILES,
            $_COOKIE
        );
    }

    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function getUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        return parse_url($uri, PHP_URL_PATH) ?? '/';
    }

    public function getQuery(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function allQuery(): array
    {
        return $this->get;
    }

    public function post(string $key, mixed $default = null ): mixed
    {
        return $this->getInputData()[$key] ?? $default;
    }

    public function allInput(): array
    {
        return array_merge($this->get, $this->getInputData());
    }

    public function getHeader(string $name, ?string $default = null): ?string
    {
        // Troca HTTP-X-FORWARDED por HTTP_X_FORWARDED
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        if($name === 'Content-Type') {
            return $this->server['CONTENT_TYPE'] ?? $default;
        }

        return $this->server[$key] ?? $default;
    }

    public function getFile(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function getInputData(): array
    {
        if ($this->jsonBody !== null) {
            return $this->jsonBody;
        }

        $contentType = $this->getHeader('Content-Type') ?? '';

        if (str_contains($contentType, 'application/json')) {
            $input = file_get_contents('php://input');
            $decoded = json_decode($input, true);
            $this->jsonBody = is_array($decoded) ? $decoded : [];
            return $this->jsonBody;
        }

        return $this->post;
    }

    /**
     * @param string $key
     * 
     * @return mixed
     */
    public function getPayload(?string $key = null): mixed
    {
        $data = $this->getInputData();

        if($key === null || $key === '') {
            return $data;
        }

        $payload = $data[$key] ?? null;

        return is_array($payload) ? $payload : $data;
    }
    
}