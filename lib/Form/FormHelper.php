<?php

namespace InetSys\Form;

use InetSys\Form\Exception\FileSaveException;
use InetSys\Form\Exception\FileSizeException;
use InetSys\Form\Exception\FileTypeException;

/**
 * Class FormHelper
 *
 * @package InetSys\Helpers
 */
class FormHelper
{
    /**
     * Получение ID формы по коду
     *
     * @param string $code
     *
     * @return int
     * @throws \Bitrix\Main\SystemException
     */
    public static function getIdByCode($code)
    {
        $dataManager = \InetSys\Constructor\EntityConstructor::compileEntityDataClass('Form', 'b_form');
        return !empty($code) ? (int)$dataManager::query()->setSelect(['ID'])->setFilter(['SID' => $code])->exec()->fetch()['ID'] : 0;
    }

    /**
     * Проверка обязательных полей формы
     *
     * @param array $fields
     * @param array $requireFields
     *
     * @return bool
     */
    public static function checkRequiredFields(array $fields, array $requireFields = [])
    {
        foreach ($requireFields as $requiredField) {
            if (empty($fields[$requiredField])) {
                return false;
                break;
            }
        }

        return true;
    }

    /**
     * Валидация email
     *
     * @param $email
     *
     * @return bool
     */
    public static function validEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Добавление результат(заполенние формы)
     *
     * @param $data
     *
     * @return bool
     */
    public static function addResult(array $data)
    {
        if (isset($data['MAX_FILE_SIZE'])) {
            unset($data['MAX_FILE_SIZE']);
        }

        $webFormId = (int)$data['WEB_FORM_ID'];

        if (isset($data['g-recaptcha-response'])) {
            unset($data['g-recaptcha-response']);
        }
        global $USER;
        $userID = 0;
        if ($USER->IsAuthorized()) {
            $userID = (int)$USER->GetID();
        }
        unset($data['web_form_submit'], $data['WEB_FORM_ID']);

        $formResult = new \CFormResult();
        $resultId = (int)$formResult->Add($webFormId, $data, 'N', $userID > 0 ? $userID : false);

        if ($resultId) {
            $formResult->Mail($resultId);
        }

        return $resultId > 0;
    }

    /**
     * Сохранение файла
     *
     * @param $fileCode
     * @param $fileSizeMb
     * @param $valid_types
     *
     * @throws FileSaveException
     * @throws FileSizeException
     * @throws FileTypeException
     * @return array
     */
    public static function saveFile($fileCode, $fileSizeMb, array $valid_types)
    {
        if (!empty($_FILES[$fileCode])) {
            $max_file_size = $fileSizeMb * 1024 * 1024;

            $file = $_FILES[$fileCode];
            if (is_uploaded_file($file['tmp_name'])) {
                $filename = $file['tmp_name'];
                /** @noinspection PassingByReferenceCorrectnessInspection */
                $ext = end(explode('.', $file['name']));
                if (filesize($filename) > $max_file_size) {
                    throw new FileSizeException('Файл не должен быть больше ' . $fileSizeMb . 'Мб');
                }
                if (!\in_array($ext, $valid_types, true)) {
                    throw new FileTypeException(
                        'Разрешено загружать файлы только с расширениями ' . implode(' ,', $valid_types)
                    );
                }
                return $file;
            }

            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new FileSizeException('Файл не должен быть больше ' . $fileSizeMb . 'Мб');
                    break;
                default:
                    throw new FileSaveException('Произошла ошибка при сохранении файла, попробуйте позже');
            }
        }

        return [];
    }

    /**
     * Добавление формы
     *
     * @param $form
     */
    public static function addForm(array $form)
    {
        $questions = [];
        if (isset($form['QUESTIONS'])) {
            $questions = $form['QUESTIONS'];
            unset($form['QUESTIONS']);
        }
        $createEmail = 'N';
        if (isset($form['CREATE_EMAIL'])) {
            $createEmail = $form['CREATE_EMAIL'];
            unset($form['CREATE_EMAIL']);
        }
        $statuses = [];
        if (isset($form['STATUSES'])) {
            $statuses = $form['STATUSES'];
            unset($form['STATUSES']);
        }
        $formId = (int)\CForm::Set($form);

        if ($formId > 0) {
            if (!empty($statuses)) {
                static::addStatuses($formId, $statuses);
            }
            if (!empty($questions)) {
                static::addQuestions($formId, $questions);
            }
            if ($createEmail === 'Y') {
                static::addMailTemplate($formId, $createEmail);
            }
        }
    }

    /**
     * Добавление статусов
     *
     * @param int   $formId
     * @param array $statuses
     */
    public static function addStatuses($formId, array $statuses)
    {
        if ($formId > 0 && !empty($statuses)) {
            $obFormStatus = new \CFormStatus();
            foreach ($statuses as $status) {
                $status['FORM_ID'] = $formId;
                $obFormStatus->Set($status);
            }
        }
    }

    /**
     * Добавление вопросов
     *
     * @param int   $formId
     * @param array $questions
     */
    public static function addQuestions($formId, array $questions)
    {
        if ($formId > 0 && !empty($questions)) {
            $obFormField = new \CFormField();
            foreach ($questions as $question) {
                $answers = [];
                if (isset($question['ANSWERS'])) {
                    $answers = $question['ANSWERS'];
                    unset($question['ANSWERS']);
                }
                $question['FORM_ID'] = $formId;
                $questionId = (int)$obFormField->Set($question);
                if ($questionId > 0 && !empty($answers)) {
                    static::addAnswers($questionId, $answers);
                }
            }
        }
    }

    /**
     * Добавление ответов
     *
     * @param array $answers
     * @param int   $questionId
     */
    public static function addAnswers($questionId, array $answers)
    {
        if ($questionId > 0 && !empty($answers)) {
            $obFormAnswer = new \CFormAnswer();
            foreach ($answers as $answer) {
                $answer['FIELD_ID'] = $questionId;
                $obFormAnswer->Set($answer);
            }
        }
    }

    /**
     * Генерация почтового шаблона
     *
     * @param int    $formId
     * @param string $createEmail
     */
    public static function addMailTemplate($formId, $createEmail = 'N')
    {
        if ($createEmail === 'Y') {
            $arTemplates = \CForm::SetMailTemplate($formId, 'Y');
            \CForm::Set(['arMAIL_TEMPLATE' => $arTemplates], $formId);
        }
    }

    /**
     * Удаление формы
     *
     * @param $sid
     */
    public static function deleteForm($sid)
    {
        $by = 'ID';
        $order = 'ASC';
        $isFiltered = false;
        $res = \CForm::GetList($by, $order, ['SID' => $sid], $isFiltered);
        while ($item = $res->Fetch()) {
            \CForm::Delete($item['ID']);
        }
    }

    /**
     * Получить реальные названия полей формы
     *
     * @param int   $formId
     * @param array $fields
     *
     * @return array
     */
    public static function getRealNamesFields($formId, array $fields = [])
    {
        $params = [
            'formId' => $formId,
        ];
        if (!empty($fields)) {
            $params['filter'] = ['SID' => $fields];
        }
        $items = static::getQuestions($params);
        $originalNames = [];
        if (!empty($items)) {
            foreach ($items as $item) {
                if (!empty($fields) && \in_array($item['SID'], $fields, true)) {
                    switch ($item['FIELD_TYPE']) {
                        case 'radio':
                        case 'dropdown':
                            $postfix = $item['SID'];
                            break;
                        case 'checkbox':
                        case 'multiselect':
                            $postfix = $item['SID'] . '[]';
                            break;
                        default:
                            $postfix = $item['ANSWER_ID'];
                    }
                    $originalNames[$item['SID']] = 'form_' . $item['FIELD_TYPE'] . '_' . $postfix;
                } elseif (empty($fields)) {
                    switch ($item['FIELD_TYPE']) {
                        case 'radio':
                        case 'dropdown':
                            $postfix = $item['SID'];
                            break;
                        case 'checkbox':
                        case 'multiselect':
                            $postfix = $item['SID'] . '[]';
                            break;
                        default:
                            $postfix = $item['ANSWER_ID'];
                    }
                    $originalNames[$item['SID']] = 'form_' . $item['FIELD_TYPE'] . '_' . $postfix;
                }
            }
        }

        return $originalNames;
    }

    /**
     * Получение вопросов
     *
     * @param array $params
     *
     * @return array
     */
    public static function getQuestions(array $params)
    {
        if ((int)$params['formId'] === 0) {
            return [];
        }
        $formId = $params['formId'];
        $by = 's_id';
        $order = 'asc';
        if (!empty($params['order'])) {
            $by = key($params['order']);
            $order = $params['order'][$by];
        }
        $filter = [];
        if (!empty($params['filter'])) {
            $filter = $params['filter'];
        }
        $type = 'ALL';
        if (!empty($params['type'])) {
            $filter = $params['type'];
        }
        $obFormField = new \CFormField();
        $isFiltered = false;
        $res = $obFormField->GetList($formId, $type, $by, $order, $filter, $isFiltered);
        $items = [];
        $obAnswer = new \CFormAnswer();
        while ($item = $res->Fetch()) {
            $isFilteredAnswer = false;
            $resAnswer = $obAnswer->GetList($item['ID'], $by, $order, ['ACTIVE' => 'Y'], $isFilteredAnswer);
            while ($itemAnswer = $resAnswer->Fetch()) {
                foreach ($itemAnswer as $key => $val) {
                    if ($key === 'ID') {
                        $item['ANSWER_ID'] = $val;
                    }
                    if (!empty($val) && empty($item[$key])) {
                        $item[$key] = $val;
                    }
                }
            }
            $items[] = $item;
        }

        return $items;
    }
}