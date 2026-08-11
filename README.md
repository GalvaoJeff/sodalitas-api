# Sodalitas API

Backend REST da **Sodalitas**, uma rede social acadêmica inspirada em Instagram, LinkedIn, Facebook e Orkut. *Sodalitas* significa "fraternidade" em latim — a proposta é um espaço para reencontrar sua turma, publicar momentos e manter viva essa fraternidade.

Este repositório contém apenas a **API**. O frontend (Vue 3 SPA) vive em um repositório separado: [`sodalitas-frontend`](https://github.com/GalvaoJeff/sodalitas-frontend).

## Stack

- **Laravel 13** / **PHP 8.4**
- **Padrão MSC** (Model–Service–Controller) — a regra de negócio fica isolada em classes `Service`, mantendo os Controllers finos (só orquestram request → service → response)
- **Laravel Sanctum** — autenticação por token (não usa sessão/cookie, é API pura consumida por uma SPA desacoplada)
- **MySQL 8**
- **Docker** — `Dockerfile` (produção) + `Dockerfile.dev` (desenvolvimento, com hot-reload via volume) + `compose.yaml` único subindo API + banco

## Arquitetura

```
app/
├── Http/
│   ├── Controllers/Api/   # Controllers finos: recebem a request, chamam o Service, devolvem JSON
│   ├── Requests/          # Form Requests com validação e mensagens em português
│   └── Resources/         # Transformação dos Models em JSON de resposta
├── Models/                 # Eloquent Models e relacionamentos
└── Services/                # Toda a regra de negócio da aplicação
```

Cada domínio (Posts, Stories, Highlights, Users, etc.) segue o mesmo fluxo:
`Controller` recebe a requisição validada pelo `FormRequest` → delega para o `Service` correspondente → o `Service` executa a lógica e retorna Models → o `Controller` serializa a resposta com um `Resource`.

## Funcionalidades

- Registro e login com token (Sanctum), senha forte obrigatória (mín. 8 caracteres, letra, número e caractere especial)
- Perfil de usuário completo (bio, avatar, localização, profissão, formação, telefone condicional a follow mútuo, hobbies)
- Busca e sugestões de usuários
- Sistema de follow (seguir/deixar de seguir)
- Feed com posts de quem o usuário segue + os próprios
- Posts com múltiplas imagens/vídeos, curtidas e comentários
- **Stories** com expiração automática de 24h
- **Destaques** (highlights) permanentes no perfil — a mídia é copiada fisicamente para um diretório próprio, ficando independente do ciclo de vida da story original mesmo após ela expirar
- Mensagens de validação 100% em português

## Como rodar

Pré-requisitos: Docker e Docker Compose instalados.

```bash
# 1. Clonar o repositório
git clone https://github.com/GalvaoJeff/sodalitas-api.git
cd sodalitas-api

# 2. Copiar o .env de exemplo
cp .env.example .env

# 3. Subir os containers (API + MySQL)
docker compose up -d --build

# As migrations rodam automaticamente na subida do container
# (ver docker/entrypoint.sh). Para popular o banco com dados de teste:
docker compose exec api php artisan db:seed
```

A API fica disponível em `http://localhost:8000/api`.

### Usuário de teste

Após rodar o seeder, um usuário fixo fica disponível para testes:

```
E-mail: teste@teste.com
Senha:  password
```

### Comandos úteis

Todo comando `artisan` deve rodar **dentro do container**, nunca direto na máquina host (o hostname `db` do MySQL só resolve na rede interna do Docker):

```bash
docker compose exec api php artisan migrate:status
docker compose exec api php artisan migrate:fresh --seed   # reseta o banco do zero
docker compose exec api php artisan storage:link           # se as imagens não carregarem
docker compose logs api -f                                  # acompanhar logs em tempo real
```

## Documentação da API (Swagger)

Com os containers no ar, a documentação interativa de todos os endpoints fica em:

```
http://localhost:8000/docs
```

A especificação OpenAPI completa está em `public/openapi.yaml`.

## Variáveis de ambiente

Principais chaves do `.env` (ver `.env.example` para a lista completa):

| Variável | Descrição |
|---|---|
| `APP_URL` | URL pública da API (usada para montar URLs absolutas de mídia com `asset()`) |
| `DB_HOST` | Nome do serviço do MySQL no `compose.yaml` (ex: `db`) — **nunca** `127.0.0.1` dentro do Docker |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Credenciais do MySQL |

## Decisões técnicas relevantes

- **PHP 8.4 obrigatório** — Laravel 13/Pest 5/Symfony 8 exigem PHP ≥8.4.
- **URLs de mídia sempre absolutas** (`asset('storage/'.$path)`), nunca relativas — evita quebra por origem cruzada entre frontend e backend.
- **Upload de perfil via method-spoofing** — `PUT` com multipart não funciona nativamente no PHP; o endpoint `/profile` espera `POST` com campo `_method=PUT`.
- **Sem `config:cache` no build da imagem Docker** — as credenciais do banco só existem em runtime; cachear no build "congela" valores incorretos.
- **Limites de upload customizados** — `docker/uploads.ini` eleva `upload_max_filesize`/`post_max_size` acima do padrão do PHP, necessário para posts com múltiplas imagens.
- **API pura, sem Inertia.js** — o projeto é intencionalmente uma API desacoplada de uma SPA Vue, não uma aplicação Laravel monolítica.

## Autor

Jeferson Galvão — [@GalvaoJeff](https://github.com/GalvaoJeff)
