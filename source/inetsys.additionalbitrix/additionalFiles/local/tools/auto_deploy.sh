#!/bin/bash

# Подключаем файл настроек
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_PATH="${DIR}/../.."

if [[ -f "${DIR}/deploy_include/deploy_settings.ini" ]]; then
    . ${DIR}/deploy_include/deploy_settings.ini
    if [[ -f "${DIR}/deploy_include/deploy_settings.${ENV_TYPE}" ]]; then
        . ${DIR}/deploy_include/deploy_settings.${ENV_TYPE}
    else
        echo "Файл настроек для зоны не найден"
        exit 25
    fi
else
    echo "Файл настроек не найден"
    exit 25
fi

# подключаем телеграм чат
#. ./deploy_include/telegram.sh

cd ${PROJECT_PATH}
#send_telegram_message "начато обновление зоны ${ENV_TYPE}";
echo "Получение изменений из git"
git pull origin ${GIT_BRANCH}
# сделать проверку на ошибки из гита
echo "Изменения из git получены"
echo "======"
cd ${COMPOSER_DIRECTORY}
if [[ ! -f "composer.phar" ]]; then
    echo "начало установки composer"
    ${INSTALLER} update
    ${INSTALLER} install curl php-cli php-mbstring git unzip
    # добавить проверку на ошибки
    curl -sS https://getcomposer.org/installer -o composer-setup.php
    ${PHP_PATH} composer-setup.php
    # добавить проверку на ошибки
    echo "composer установлен"
    echo "======"
fi
if [[ -f "composer.phar" && -f "composer.json" ]]; then
    echo "начало установки пакетов из composer"
    if [[ "${ENV_TYPE}" = "dev" ]]; then
        ${PHP_PATH} composer.phar install
    else
        ${PHP_PATH} composer.phar install --no-dev
    fi
    # добавить проверку на ошибки
    echo "Установка пакетов из composer завершена"
    echo "======"
fi
cd ${PROJECT_PATH}
if [[ -f "${MIGRATE_PATH}" ]] && ( [[ -d "local/modules/${MIGRATE_MODULE_NAME}" ]] || [[ -d "bitrix/modules/${MIGRATE_MODULE_NAME}" ]] ); then
    echo "начало миграций"
    ${PHP_PATH} ${MIGRATE_PATH} up
    # добавить проверку на ошибки
    echo "Миграции установлены"
    echo "======"
fi
if [[ "${ENV_TYPE}" = "prod" ]]; then
    git remote prune origin
    echo "Очистка git завершена"
    echo "======"
fi
#send_telegram_message "обновление зоны ${ENV_TYPE} завершено успешно";