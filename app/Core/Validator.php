<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function validate(array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleList) {
            $value = $this->data[$field] ?? null;
            $rulesArr = is_string($ruleList) ? explode('|', $ruleList) : $ruleList;
            foreach ($rulesArr as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $rule = trim($rule);
                if ($rule === '') {
                    continue;
                }
                $method = 'rule' . str_replace(' ', '', ucwords(str_replace('_', ' ', $rule)));
                if (method_exists($this, $method)) {
                    $ok = $this->$method($field, $value, $params);
                    if (!$ok) {
                        break;
                    }
                }
            }
        }
        return count($this->errors) === 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field = ''): ?string
    {
        if ($field === '') {
            foreach ($this->errors as $list) {
                if (is_array($list) && count($list)) {
                    return $list[0];
                }
            }
            return null;
        }
        return $this->errors[$field][0] ?? null;
    }

    // --- Rules ---
    private function ruleRequired(string $field, mixed $value): bool
    {
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            $this->addError($field, "El campo $field es obligatorio.");
            return false;
        }
        return true;
    }

    private function ruleEmail(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!filter_var((string)$value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El campo $field debe ser un correo válido.");
            return false;
        }
        return true;
    }

    private function ruleMinLength(string $field, mixed $value, array $params): bool
    {
        $min = (int)($params[0] ?? 0);
        if ($value === null || $value === '') {
            return true;
        }
        if (mb_strlen((string)$value) < $min) {
            $this->addError($field, "El campo $field debe tener al menos $min caracteres.");
            return false;
        }
        return true;
    }

    private function ruleMaxLength(string $field, mixed $value, array $params): bool
    {
        $max = (int)($params[0] ?? 999999);
        if ($value === null || $value === '') {
            return true;
        }
        if (mb_strlen((string)$value) > $max) {
            $this->addError($field, "El campo $field no puede exceder $max caracteres.");
            return false;
        }
        return true;
    }

    private function ruleInteger(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, "El campo $field debe ser un número entero.");
            return false;
        }
        return true;
    }

    private function ruleNumeric(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!is_numeric($value)) {
            $this->addError($field, "El campo $field debe ser numérico.");
            return false;
        }
        return true;
    }

    private function ruleDate(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        $dt = \DateTime::createFromFormat('Y-m-d', (string)$value);
        $ok = $dt && $dt->format('Y-m-d') === (string)$value;
        if (!$ok) {
            $this->addError($field, "El campo $field debe ser una fecha válida (YYYY-MM-DD).");
            return false;
        }
        return true;
    }

    private function ruleIn(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!in_array((string)$value, $params, true)) {
            $this->addError($field, "Valor no permitido para $field.");
            return false;
        }
        return true;
    }

    private function ruleUrl(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!filter_var((string)$value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "El campo $field debe ser una URL válida.");
            return false;
        }
        return true;
    }

    private function ruleMatches(string $field, mixed $value, array $params): bool
    {
        $other = $params[0] ?? '';
        $otherValue = $this->data[$other] ?? null;
        if ((string)$value !== (string)$otherValue) {
            $this->addError($field, "El campo $field no coincide con $other.");
            return false;
        }
        return true;
    }

    private function ruleSlug(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string)$value)) {
            $this->addError($field, "Formato inválido. Usa solo letras minúsculas, números y guiones.");
            return false;
        }
        return true;
    }

    private function addError(string $field, string $msg): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $msg;
    }
}
