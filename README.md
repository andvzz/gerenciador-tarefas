# Gerenciador de Tarefas — CodeIgniter 4

Sistema CRUD completo de gerenciamento de tarefas construído com **CodeIgniter 4.7** e **Tailwind CSS**, incluindo proteção CSRF, validação no Model e uma **API REST** em JSON.

---

## Funcionalidades

- ✅ CRUD completo (Criar, Listar, Editar, Excluir) com rotas amigáveis
- ✅ Validação de dados nativa do CodeIgniter no Model
- ✅ Proteção CSRF habilitada globalmente
- ✅ Proteção contra SQL Injection (Query Builder / Active Record do CI4)
- ✅ Quadro Kanban responsivo (Tailwind CSS) com drag & drop entre status
- ✅ API REST (`/api/tarefas`) retornando JSON

---

## Requisitos

- PHP **8.2** ou superior
- Extensões: `intl`, `mbstring`, `mysqli` (ou `pdo_mysql`)
- MySQL / MariaDB
- (Opcional) Composer — **o framework já está instalado** na pasta `vendor/`, então não é obrigatório.

---

## 1. Configuração do Banco de Dados

1. Abra o seu cliente MySQL (phpMyAdmin, DBeaver, linha de comando, etc.).
2. Execute o script `database.sql` localizado na raiz do projeto. Ele cria o banco
   `meu_banco`, a tabela `tasks` e alguns registros de exemplo.

   Via linha de comando:

   ```bash
   mysql -u root -p < database.sql
   ```

---

## 2. Configuração do Ambiente (`.env`)

O arquivo `.env` já está na raiz do projeto. Ajuste as credenciais do banco
substituindo os **placeholders** pelos seus dados reais:

```dotenv
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = meu_banco        # nome do seu banco
database.default.username = meu_usuario      # seu usuário
database.default.password = minha_senha      # sua senha
database.default.DBDriver = MySQLi
database.default.port = 3306
```

> O ambiente já está definido como `development`, o que exibe erros detalhados
> e a Debug Toolbar — ideal para desenvolvimento.

---

## 3. Rodando o Servidor

Na raiz do projeto, execute o servidor embutido do CodeIgniter:

```bash
php spark serve
```

A aplicação ficará disponível em:

- **App Web:** http://localhost:8080/tarefas
- **Raiz (redireciona para a lista):** http://localhost:8080/

Para usar outra porta:

```bash
php spark serve --port 9000
```

---

## 3.1 Front-end (build do Tailwind CSS)

A interface usa **Tailwind CSS** com a identidade visual azul/branco (Madalozzo).
O CSS é compilado para um arquivo estático e minificado em
`public/assets/css/app.css` (servido em `/assets/css/app.css`).

> O arquivo já vem compilado no repositório, então a aplicação roda sem nenhum
> passo extra. Você só precisa do build abaixo se **alterar as views** (HTML/classes).

Requisitos: **Node.js 18+** e **npm**.

```bash
# 1. Instalar as dependências de front-end (uma vez)
npm install

# 2. Gerar o CSS minificado para produção
npm run build:css

# 3. (Opcional) Durante o desenvolvimento, recompila ao salvar as views
npm run watch:css
```

Estrutura do pipeline:

| Arquivo                       | Função                                            |
|-------------------------------|---------------------------------------------------|
| `resources/css/input.css`     | Entrada: diretivas Tailwind + estilos premium     |
| `tailwind.config.js`          | Paleta `brand` (azul), fontes e arquivos escaneados |
| `public/assets/css/app.css`   | Saída compilada (referenciada pelo layout)        |

---

## 4. Rotas da Aplicação Web

| Método | URL                          | Ação                          |
|--------|------------------------------|-------------------------------|
| GET    | `/tarefas`                   | Lista todas as tarefas        |
| POST   | `/tarefas/salvar`            | Salva a nova tarefa           |
| POST   | `/tarefas/atualizar/{id}`    | Salva a edição                |
| POST   | `/tarefas/atualizar-status`  | Atualiza apenas o status      |
| GET    | `/tarefas/excluir/{id}`      | Exclui a tarefa               |

---

## 5. API REST

Base URL: `http://localhost:8080/api/tarefas`

As rotas da API são **isentas de CSRF** (configurado em `app/Config/Filters.php`),
pois consomem JSON.

| Método | Endpoint              | Descrição                  |
|--------|-----------------------|----------------------------|
| GET    | `/api/tarefas`        | Lista todas as tarefas     |
| GET    | `/api/tarefas/{id}`   | Retorna uma tarefa         |
| POST   | `/api/tarefas`        | Cria uma nova tarefa       |
| PUT    | `/api/tarefas/{id}`   | Atualiza uma tarefa        |
| DELETE | `/api/tarefas/{id}`   | Exclui uma tarefa          |

### Exemplos com `curl`

**Listar:**
```bash
curl http://localhost:8080/api/tarefas
```

**Criar:**
```bash
curl -X POST http://localhost:8080/api/tarefas \
  -H "Content-Type: application/json" \
  -d '{"title":"Estudar CodeIgniter","description":"Ler a documentação","status":"pendente"}'
```

**Atualizar:**
```bash
curl -X PUT http://localhost:8080/api/tarefas/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Estudar CI4","status":"concluída"}'
```

**Excluir:**
```bash
curl -X DELETE http://localhost:8080/api/tarefas/1
```

---

## Estrutura dos Arquivos Principais

```
gerenciador-tarefas/
├── database.sql                              # Script do banco de dados
├── .env                                      # Configurações de ambiente
├── app/
│   ├── Config/
│   │   ├── Routes.php                         # Rotas web + grupo /api
│   │   └── Filters.php                        # CSRF global (exceto api/*)
│   ├── Models/
│   │   └── TarefaModel.php                    # Active Record + validação
│   ├── Controllers/
│   │   ├── TarefaController.php               # CRUD web
│   │   └── Api/
│   │       └── TarefaApiController.php        # API REST (ResourceController)
│   └── Views/
│       ├── layout/main.php                    # Layout base
│       └── tarefas/
│           └── index.php                      # Quadro Kanban (CRUD via modal/AJAX)
└── public/                                    # Document root (index.php)
```

---

## Segurança

- **CSRF:** habilitado em `app/Config/Filters.php` (`$globals['before']`), aplicado a
  todas as requisições web. Os formulários incluem `<?= csrf_field() ?>`.
- **SQL Injection:** todas as operações usam o Query Builder / Model do CI4, que
  faz o bind/escape dos parâmetros automaticamente.
- **Mass Assignment:** o Model restringe os campos graváveis via `$allowedFields`.
- **XSS:** as views aplicam `esc()` em todas as saídas dinâmicas.

---

## Reinstalando dependências (opcional)

Caso a pasta `vendor/` seja removida e você tenha o Composer instalado:

```bash
composer install
```
