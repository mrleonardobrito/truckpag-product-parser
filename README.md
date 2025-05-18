# TruckPag Product Parser

Uma API RESTful para importar, armazenar e gerenciar informações nutricionais de produtos alimentícios a partir do Open Food Facts, facilitando a revisão por nutricionistas.

---

## Tecnologias Utilizadas

-   **Linguagem:** PHP 8.3+
-   **Framework:** Laravel 12
-   **Banco de Dados:** MongoDB (com integração via `mongodb/laravel-mongodb`)
-   **Gerenciamento de Dependências:** Composer
-   **Testes:** PHPUnit
-   **Documentação:** L5 Swagger (OpenAPI 3.0)
-   **Containerização:** Docker & Docker Compose

---

## Instalação e Uso

### Pré-requisitos

-   Docker e Docker Compose instalados **OU** ambiente local com PHP 8.2+, Composer e MongoDB.

### Passos a passo para rodar a aplicação

1. Clone o repositório:

    ```bash
    git clone https://github.com/mrleonardobrito/truckpag-product-parser
    cd truckpag-product-parser
    ```

2. Copie o arquivo de variáveis de ambiente:

    ```bash
    cp .env.example .env
    ```

3. Suba os containers:

    ```bash
    docker-compose up -d
    ```

4. Instale as dependências do PHP:

    ```bash
    composer install
    ```

5. (Opcional) utilize o comando `php artisan app:import-open-foods-facts` para importar os produtos através do open-foods

6. Rode a aplicação através do artisan:

    ```bash
    php artisan serve
    ```

7. Acesse a API em `http://localhost:8000`
8. Acesse a documentação através de `http://localhost:8000/api/documentation` ou importando no postman através do arquivo `postman_collection.json`

---

## Funcionalidades

-   Importação diária automatizada dos produtos do Open Food Facts (limitada a 100 produtos por arquivo).
-   CRUD completo de produtos via API REST.
-   Paginação de resultados.
-   Histórico de importações.
-   Testes unitários para endpoints principais.
-   Documentação interativa via Swagger.
-   Pronto para uso em containers Docker.

---

## Cronjob de Importação Automática

O projeto conta com um cronjob configurado para importar diariamente os produtos do Open Food Facts para o banco de dados MongoDB. O agendamento é feito via Laravel Scheduler e está definido para rodar todos os dias às 06:32 (horário de São Paulo).

### Como funciona:

-   O comando `app:import-open-foods-facts` verifica se há recursos suficientes de CPU e memória antes de iniciar a importação.
-   O importador é implementado em Go e é responsável por baixar, extrair e processar os arquivos de produtos, limitando a 100 produtos por arquivo.
-   Cada produto importado recebe os campos personalizados `imported_t` (data/hora da importação) e `status` (published, draft ou trash).
-   O histórico de cada execução é salvo em uma collection secundária, permitindo auditoria e rastreabilidade.
-   O horário da última execução do cronjob pode ser consultado pelo endpoint raiz da API (`GET /`).
-   O log detalhado da execução é salvo em `storage/logs/import-open-foods-facts.log`.

---

> Este é um challenge by [Coodesh](https://coodesh.com/)

---
