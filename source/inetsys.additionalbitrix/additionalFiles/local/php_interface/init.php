<?php
use WebArch\BitrixNeverInclude\BitrixNeverInclude;

/** настрйоки */
require_once __DIR__ . '/settings.php';

/** автоподгрузка из композера для вендора */
require_once $_SERVER['DOCUMENT_ROOT'] . PATH_TO_VENDOR_AUTOLOAD;
/** автоподгрузка модулей битиркса, чтобы их не надо было подключать */
BitrixNeverInclude::registerModuleAutoload();

/** регистрация событий */
require_once __DIR__ . '/eventRegister.php';