# Tyto HTTP (v0.0.1)

O **Tyto HTTP** é um componente independente de manipulação de requisições e respostas HTTP para PHP 8.5+, projetado sem acoplamento com frameworks ou routers específicos. O seu objetivo é fornecer uma API limpa, imutável, segura e orientada a objetos para o ciclo de vida HTTP.

---

## Sumário

1. [Filosofia do Projeto]()
2. [Requisitos do Sistema]()
3. [Instalação]()
4. [Classe Request (`Tyto\Http\Request`)]()
* [Criação e Bootstrap]()
* [Métodos de Acesso a Dados (Query, Post, Headers, Server)]()
* [Manipulação de Arquivos e Uploads]()


5. [Classe Response (`Tyto\Http\Response`)]()
* [Gestão de Status e Headers]()
* [Respostas em JSON e Conteúdo Estático]()
* [Padrão de Imutabilidade (With Methods)]()


6. [Integração com o Tyto Router]()

---

## Filosofia do Projeto

O componente `tyto-http` segue rigorosamente os princípios de **imutabilidade** e **separação de responsabilidades**. Enquanto a requisição (`Request`) encapsula de forma limpa e segura o estado global do ambiente PHP (`$_GET`, `$_POST`, `$_SERVER`, `$_FILES`), a resposta (`Response`) permite construir o fluxo de saída HTTP de maneira encadeada e previsível.

---

## Requisitos do Sistema

* **PHP:** ^8.5 ou superior
* **Extensões:** `filter`, `mbstring`

---

## Instalação

Adicione o componente ao seu projeto via repositório local ou Composer:

```bash
composer require tyto-php/http

```

---

## Classe Request (`Tyto\Http\Request`)

A classe `Request` é responsável por abstrair os dados de entrada da requisição HTTP de maneira orientada a objetos.

### 1. Criação e Bootstrap (`createFromGlobals`)

Para instanciar a requisição capturando automaticamente os globais do PHP (`$_GET`, `$_POST`, `$_SERVER`, `$_COOKIE`, `$_FILES`), utilize o método de fábrica estático:

```php
<?php

declare(strict_types=1);

use Tyto\Http\Request;

$request = Request::createFromGlobals();

```

### 2. Métodos de Acesso a Dados

A classe disponibiliza métodos seguros para recuperar parâmetros de entrada, filtrando e prevenindo acessos a índices inexistentes:

```php
// Recuperar o método HTTP (GET, POST, PUT, DELETE, etc.)
$method = $request->getMethod(); // string

// Recuperar a URI ou Caminho atual
$uri = $request->getUri(); // string

// Recuperar parâmetros de Query String ($_GET)
$search = $request->query('q', 'valor_padrao'); // mixed
$allQuery = $request->getQuery(); // array completo

// Recuperar dados de corpo/payload ($_POST ou JSON)
$email = $request->getPayload('email'); // mixed

// Recuperar cabeçalhos HTTP específicos
$userAgent = $request->header('User-Agent'); // string|null

```

---

## Classe Response (`Tyto\Http\Response`)

A classe `Response` controla o código de status HTTP, os cabeçalhos (`headers`) e o corpo (`body`) da resposta enviada ao cliente.

### 1. Inicialização Básica

```php
<?php

declare(strict_types=1);

use Tyto\Http\Response;

// Criando uma resposta com texto simples e status 200 OK
$response = new Response('Olá, Mundo!', 200);

```

### 2. Respostas em JSON

Ideal para APIs RESTful modernas, serializando arrays ou objetos automaticamente com o header `Content-Type: application/json`:

```php
return Response::json([
    'success' => true,
    'data' => [
        'id' => 1,
        'name' => 'Tyto Framework'
    ]
], 201);

```

### 3. Padrão de Imutabilidade (`With Methods`)

Seguindo os padrões modernos de arquitetura, os métodos de modificação da resposta geram uma **nova instância clonada**, preservando a original (imutabilidade):

```php
$response = (new Response('Conteúdo protegido'))
    ->withStatus(401)
    ->withHeader('X-Custom-Header', 'Tyto-Security')
    ->withAddedHeader('Cache-Control', 'no-store');

```

### 4. Envio Efetivo da Resposta

Para despachar os headers e o corpo da resposta para o cliente web:

```php
$response->send();

```

---

## Integração com o Tyto Router

O `Tyto\Http\Request` integra-se perfeitamente com o **Tyto Router** através da Injeção de Dependências baseada na API de Reflexão do PHP. Ao declarar a classe `Request` na assinatura do método do seu Controller, o roteador injeta a instância capturada automaticamente:

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Tyto\Router\Routing\Attributes\Post;
use Tyto\Http\Request;
use Tyto\Http\Response;

final class UserController
{
    #[Post('/users')]
    public function store(Request $request): Response
    {
        // Obtém os dados enviados via requisição HTTP
        $name = $request->input('name');
        $email = $request->input('email');

        // Processa a regra de negócio...

        // Retorna uma Resposta HTTP padronizada
        return Response::json([
            'message' => "Usuário {$name} criado com sucesso!"
        ], 201);
    }
}

```