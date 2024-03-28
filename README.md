[![.github/workflows/gitpuller.yml](../../actions/workflows/gitpuller.yml/badge.svg)](../../actions/workflows/gitpuller.yml)

# GITPULLER

**GITPULLER** - WebHook публикатор для интеграции с GitHub. Публикатор синхронизирует состояние репозитория, в котором он установлен и его плагин в ``./user/**/`` посредствам вызова из [github/workflow](https://docs.github.com/en/actions/using-workflows).

## Установка
Для успешной связки основной проект должен быть GIT-репозиторием (далее "основной репозиторий"), ровно как и его плагины (далее "плагины-субрепозитории"), находящихся в ``./user/**/``.

### Основной репозиторий

Для минимального функционала требуется добавить в репозиторий следующие файлы:
- [gitpuller.php](/gitpuller.php) - webhook приёмник.
- [.github/workflows/gitpuller_tmpl.yml](.github/workflows/gitpuller_tmpl.yml) - GitHub CI workflow шаблон с инструкциями публикации.
- [.github/workflows/gitpuller.yml](.github/workflows/gitpuller.yml) - GitHub CI workflow инструкция публикации, наследующая шаблон ``gitpuller_tmpl.yml``. Наличие этого файла активизирует CI workflow на GitHub.

### Субрепозитории
Плагины-субрепозитории должны быть клонированы в ``./user/**/`` основного репозитория на удалённом сервере вручную (конфигурация соотв. репозиториев на GitHub установку не произведёт). Названия директорий, куда субрепозитории будут клонированы, должны соответствовать названиям репозиториев на GitHub: 
 - ``https://github.com/rotenbaron/grav-main-slot`` -> ``./user/plugins/grav-main-slot``
 - ``https://github.com/hellkaim/vendure-connector/`` -> ``./user/plugins/vendure-connector``

Для синхронизации плагин-субрепозиториев требуется активировать для них CI workflow, добавив файл ``.github/workflows/gitpuller.yml``, схожий с [gitpuller.yml](.github/workflows/gitpuller.yml), но содержащий точный путь к CI-шаблону [gitpuller_tmpl.yml](.github/workflows/gitpuller_tmpl.yml) основного репозитория. Инструкция ``uses`` в данном примере указывает на репозиторий ``rotenbaron/grav-main-slot`` - это значение требуется заменить на соотв. основного репозитория. Инструкция ``jobs.deployment.with.MAIN_SLOT_REPO`` должна отсутствовать или быть равной ``false``.
```yml
on: push
jobs:
  deployment:
    uses: "rotenbaron/grav-main-slot/.github/workflows/gitpuller_tmpl.yml@main"
    with: 
      GITPULLER_HOST: ${{ vars.GITPULLER_HOST }}
      REPO_BRANCH: ${{ vars.REPO_BRANCH }}
    secrets: inherit
```

## Конфигурация

### На сервере публикации
- Требуется удостоверится, что директории, как основного репозитория, так и плагин-субрепозиториев, принадлежат пользователю, запускающему PHP скрипты (как правило ``www-data``).
- [.gitignore](.gitignore) в основном репозитории должен содержать исключения ``user/plugins`` и ``user/themes``, а также ``gitpuller_config.php``.
- ``gitpuller_config.php`` - опциональный файл настройки для [gitpuller.php](/gitpuller.php)
  ```php
  <?php
  $gitpuller_config = [
      'GITPULLER_KEY' => 'FOO123', # секретный ключ запроса. Необязателен
      'DEBUG' => false # вывод отладочных данных о запросе в отчёт CI actions
  ];
  ```

### На GitHub

- [Настройки secrets](../../settings/secrets/actions):
  - ``GITPULLER_KEY`` (опциональный) - секретный ключ запроса. Необязателен, если не [настроен на сервере](#на-сервере-публикации), в ``gitpuller_config.php``.
- [Настройки variables](../../settings/variables/actions):
  - ``GITPULLER_HOST`` - внешний адрес удалённого сервера, где находится webhook. Должен начинаться с протокола и заканчиваться доменом. без конечного ``/``.
  - ``REPO_BRANCH`` - отслеживаемая ветка репозитория. При наличии, будет автоматически переключена на удалённом сервере перед синхронизацией.

## Использование

После конфигурации все ``push`` запросы в отслеживаемые ветки на GitHub, будь то в основной репозиторий или плагины-субрепозитории будет автоматически синхронизировать соотв. репозитории на удалённом сервере, запуская на нём команду ``pull``. Все неучтённые изменения на удалённом сервере будут отброшены, а вручную переключенные ветки - будут переключены на отлеживаемые. После конфигурации GITPULLER, репозитории на удалённом сервере переходят в режим авто-синхронизации с GitHub и любые прямые манипуляции с ними в обход - становятся нежелательными.