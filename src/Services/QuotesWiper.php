<?php

namespace App\Services;

/**
 * Class QuotesWiper - Удаление спецсимволов для избежания SQL иньекций, когда bindparam невозможен
 * @package App\Services
 */
class QuotesWiper
{
    /**
     * @param string|null $value
     * @return mixed|null
     */
    public static function filterString(?string $value)
    {
        $filtered = filter_var($value, FILTER_SANITIZE_STRING);
        return $filtered ? $filtered : null;
    }

    /**
     * removeAll() - удалить все спецсимволы. Применять для обработки имен колонок
     * @param string $value
     * @return mixed|string
     */
    public static function removeAll(?string $value): ?string
    {
        // УБИРАЕМ:
        // Символ ASCII NUL (\0)
        // Обратный символ (\b)
        // Control + Z.(\Z)
        // Символ обратной косой черты ( "\" )
        // Символ новой строки (\n)
        // Символ возврата каретки (\r)
        // Символ табуляции (/t)
        // Символ одиночной кавычки (')
        // Символ двойной кавычки (")
        // Символ "%"
        // Пробелы
        // Символ ";"
        // Символ "-"
        // Символ "<"
        // Символ ">"
        if ($value != "") {
            $badchar = array("\0", "\b", "\Z", "\\", "\n", "\r", "\t", "'", "\"", "%", " ", ";", "-", "<", ">");
            $value = str_replace($badchar, '', $value);
            $value = htmlspecialchars($value);
        } else {
            $value = null;
        }

        return $value;
    }

    /**
     * removeQuotes() - Удаление кавычек. Применять для текстовых полей, когда заведомо известно,
     * что кавычек в нем не должно быть
     *
     * @param string $value
     * @return mixed|string
     */
    public static function removeQuotes(?string $value): ?string
    {
        // УБИРАЕМ:
        // Символ ASCII NUL (\0)
        // Обратный символ (\b)
        // Control + Z.(\Z)
        // Символ обратной косой черты ( "\" )
        // Символ одиночной кавычки (')
        // Символ двойной кавычки (")
        if ($value != "") {
            $badchar = array("\0", "\b", "\Z", "\\", "'", "\"");
            $value = str_replace($badchar, '', $value);
            $value = htmlspecialchars($value);
        } else {
            $value = null;
        }
        return $value;
    }

    /**
     * slashQuotes() - Экранирование кавычек
     * @param string $value
     * @return mixed|string
     */
    public static function slashQuotes(?string $value): ?string
    {
        // УБИРАЕМ:
        // Символ ASCII NUL (\0)
        // Обратный символ (\b)
        // Control + Z.(\Z)
        // Символ обратной косой черты ( "\" ).

        //ЭКРАНИРУЕМ:
        // Символ одиночной кавычки (')
        // Символ двойной кавычки (")
        if ($value === null || $value == "") {
            $value = null;
        } else {
            $badchar = array("\0", "\b", "\Z", "\\");
            $value = str_replace($badchar, '', $value);
            $value = addslashes($value);
            $value = htmlspecialchars($value);
        }

        /*
            Иногда функцию addslashes() некорректно пытаются использовать для предотвращения SQL-инъекций.
            Не делайте так. Вместо нее используйте подготовленные запросы или функции экранирования
            соответствующих расширений работы с базами данных.
         */
        return $value;
    }


    public static function slashDates(?string $value): ?string
    {
        // УБИРАЕМ:
        // Символ ASCII NUL (\0)
        // Обратный символ (\b)
        // Control + Z.(\Z)
        // Символ обратной косой черты ( "\" )
        // Символ новой строки (\n)
        // Символ возврата каретки (\r)
        // Символ табуляции (/t)
        // Символ одиночной кавычки (')
        // Символ двойной кавычки (")
        // Символ "%"
        // Пробелы
        // Символ ";"
        // Символ "<"
        // Символ ">"
        if ($value != "") {
            $value = str_replace('.', '-', $value);
            //TODO if date 20.12.2019 - > 2019-12-20


            $value = preg_replace('~[^0-9T\s:\+\-\/]+~', '', $value);
            $value = str_replace('--', '', $value);
            $value = htmlspecialchars($value);
        } else {
            $value = null;
        }
        return $value;
    }

    public static function slashBase64(?string $value): ?string
    {
        $value = preg_replace('/([^0-9a-zA-Z\/\+;:,])+/', '', $value);
        return $value;
    }

    public static function slashBoolean($value, $nullable = false): ?bool
    {
        if ($value === true) {
            return true;
        } else {
            if ($nullable) {
                if ($value == null or $value == "") {
                    return null;
                } else {
                    return $value == "true" ? true : false;
                }
            } else {
                return $value == "true" ? true : false;
            }
        }
    }

    public static function slashInteger($value): ?int
    {
        if ($value === null || $value == "") {
            return null;
        } else {
            return intval($value);
        }
    }

    public static function slashFloat($value): ?float
    {
        if ($value === null || $value == "") {
            return null;
        } else {
            $value = str_replace(',', '.', $value);
            return floatval($value);
        }
    }

    public static function slashArrayOfIntegersToStringSeparatedByComma(array $values): string
    {
        $values = array_map(function ($val) {
            return intval($val);
        }, $values);

        return '' . join(',', $values);
    }
    public static function slashStringsOfIntegersToStringSeparatedByComma(string $values): string
    {
        $pattern = '/,\s+/i';
        $replacement = ',';
        $values = preg_replace($pattern, $replacement, $values);
        $values = explode(',', $values);
        return QuotesWiper::slashArrayOfIntegersToStringSeparatedByComma($values);
    }
}
