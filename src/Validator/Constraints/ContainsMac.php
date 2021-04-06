<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ContainsMac extends Constraint
{
    public $message = 'Строка "{{ string }}" содержит некорректный мак-адрес';
}