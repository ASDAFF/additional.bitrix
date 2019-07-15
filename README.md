# Расширенные функции для Битрикс

**Описание**
Компонент
BaseBitrixComponent
Базовый класс для упрощения создания компонентов и их унификации

Особенности:

Включен логгер
Можно задать ключи кеширования
Можно переопределить вызываемый шаблон через метод
Изначально включен кеш
Все необходимые действия делать в этом классе prepareResult. Если логика сложнее, то переопределяем execute

**Конструктор**
Делает возможным работы с dataManager, если сущность не описана

Базовый конструктор(EntityConstructor)
```php
$dataManager = \InetSys\Constructor\EntityConstructor::compileEntityDataClass('Form', 'b_form'); 
//дальше работаем как обычно с объектом 
$id = (int)$dataManager::query()->setSelect(['ID'])->setFilter(['SID' => $code])->exec()->fetch()['ID'];
```

Упрощенный  конструктор для свойств инфоблока в отдельной  таблице(IblockPropEntityConstructor и  IblockPropMultipleEntityConstructor)
```php
$dataManager = \InetSys\Constructor\IblockPropEntityConstructor::getDataClass($iblockId); $dataManager = \Vf92\Constructor\IblockPropMultipleEntityConstructor::getDataClass($iblockId); 
//дальше работаем как обычно с объектом 
$id = (int)$dataManager::query()->setSelect(['ID'])->setFilter(['CODE' => $code])->exec()->fetch()['ID'];
```

**Пользователь и группы пользователя**

UserGroupHelper
хелпер для получения данных из групп пользователя

getGroupIdByCode - Возвращает id группы пользователей по её коду
UserHelper
Хелпер для получения данных пользователя

isInGroup - Проверяет вхождение пользователя в группу
getLoginByHash - Возвращает логин пользователя по хешу его запомненной авторизации

**Инфоблоки**

IblockHelper
Хелпер для инфоблока

getIblockId - Возвращает id инфоблока по его типу и символьному коду
getIblockXmlId - Возвращает xml id инфоблока по его типу и символьному коду
getPropertyId - Возвращает id свойства инфоблока по символьному коду
isIblockTypeExists - Проверка существования типа инфоблоков

**Хайлоад блоки**

HLBlockHelper
получение информации о highload блоке, например, id по названию таблицы

getIdByName - Получение ID Хайлоад блока по имени
getIdByTableName - Получение ID Хайлоад блока по таблице
HLBlockFactory
создание объекта dataManager

createTableObject - Возвращает скомпилированную сущность HL-блока по имени его сущности.
createTableObjectByTable - Возвращает скомпилированную сущность HL-блока по имени его таблицы в базе данных.

**Форма**

FormHelper
getIdByCode - Получение ID формы по коду
checkRequiredFields - Проверка обязательных полей формы
validEmail - Валидация email
addResult - Добавление результата(заполнение формы)
saveFile - Сохранение файла
addForm - Добавление формы
addStatuses - Добавление статусов
addQuestions - Добавление вопросов
addAnswers - Добавление ответов
addMailTemplate - Генерация почтового шаблона
deleteForm - Удаление формы
getRealNamesFields - Получить реальные названия полей формы
getQuestions - Получение вопросов

**Декораторы**

FullHrefDecorator
позволяет получить абсолютный путь сайта по относительному

```php
$fullPath = (new \InetSys\Decorators\FullHrefDecorator($path))->getFullPublicPath();
```

**Хелперы**

ClassFinderHelper
Получение списка классов

getClasses - Поиск классов с совпадением имени в определенной папке
DateHelper
Хелпер для работы с датами

replaceRuMonth - Подстановка русских месяцев по шаблону
replaceRuDayOfWeek - Подстановка дней недели по шаблону
convertToDateTime - Преобразование битриксового объекта даты в Php
formatDate - Враппер для FormatDate. Доп. возможности
ll - отображение для недели в винительном падеже (в пятницу, в субботу)
XX - 'Сегодня', 'Завтра'
PhoneHelper
Обработка и нормализация телефонов

isPhone - Проверяет телефон по правилам нормализации. Допускаются только десятизначные номера с ведущими 7 или 8
normalizePhone - Нормализует телефонный номер.
Возвращает телефонный номер в формате xxxxxxxxxx (10 цифр без разделителя)
Кидает исключение, если $phone - не номер
formatPhone - Форматирует телефон по шаблону
TaggedCacheHelper
Класс для упрощенной работы с тегированным кешем; есть 2 режима работы: как static, так и dynamic(через объект)

addManagedCacheTags - Добавление тегов массивом
clearManagedCache - Очистка кеша по тегам
addManagedCacheTag - Добавление одного тега
getTagCacheInstance - Получение объекта тегированного кеша
start - Начинаем тегированный кеш
end - Завершаем тегированный кеш
addTags - Добавляем теги
addTag - Добавляем тег
abortTagCache - прерываем тегированный кеш(abort)
WordHelper
Класс для работы со словами, например, окончания

declension - Возвращает нужную форму существительного, стоящего после числительного
showWeight - Возвращает отформатированный вес
showLengthByMillimeters - Возвращает отформатированную длину в см - задается в мм
numberFormat - Форматированный вывод чисел, с возможностью удаления незначащих нулей и с округлением до нужной точности
clear - Очистка текста от примесей(тегов, лишних спец. символов)

**Дополнительные возможности для запросов к Mysql через объект dataQuery**

MysqlBatchOperations

Массовые операции над таблицами с поддержкой условий

batchUpdate - Делаем массовое обновление данных по условию
batchDelete - Делаем массовое удаление по условию
batchInsert - Делаем массовую вставку
getPart - Получение части массива по лимтам
getLimit - Получаем ограничение в limit
setLimit - Устанавливаем ограничение в limit
getTable - Получаем имя таблицы
setTable - Устанавливаем имя таблицы
getQuery - Получение установленного объекта Query
setQuery - Установка объекта Query
ExtendsBitrixQuery
Получение сформированных запросов(селекта,фильтра)

getBuildWhere - Получаем сформированное условие по запросу(where)
getBuildOrder - Получаем сформированную сортировку(order)

**Другие возможности** 

BitrixUtils

Нераспределенные функции:

isAjax - битриксовая проверка на аякс
bool2BitrixBool - преобразование из буля в битриксовый буль
bitrixBool2bool - преобразование из битриксового буля в буль
MiscUtils

Нераспределенные функции:

getClassName - получение имени класса без namespace
папка additionalFiles
В папке содержатся доп. файлы, которые могут понадобиться на проекте - это базовый композер, gitignore для битрикса и cs_fixer