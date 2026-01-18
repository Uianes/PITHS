# PITHS (base web)

Mini-base PHP + MySQL + Bootstrap (CDN) para o PITHS.

## Requisitos
- PHP 8+
- MySQL/MariaDB
- Banco `piths` importado a partir do seu `piths.sql`

## Configuração
Edite `includes/config.php` com usuário/senha do seu MySQL.

## Fluxo
- `index.php` → landing infantil com fundo e mascote
- `cadastro.php` → cria usuário com `ACTIVE=0` (pendente)
- `login.php` → login por **matrícula + senha**
- Se `ACTIVE=0` → `aguardando_validacao.php`
- Se `ACTIVE=1` → redireciona por perfil:
  - ADM → `/admin/index.php`
  - PROF → `/professor/index.php`
  - ALUNO → `/aluno/index.php`

## Professor
- Visualiza turmas vinculadas em `PROF_TURMA`
- Pode adicionar atividade por **PATH** e vincular à turma (`ATIVIDADE` + `ATIVIDADE_TURMA`)

## Aluno
- Vê atividades da sua turma; se vazio mostra: "Aguardando seu professor selecionar as atividades".