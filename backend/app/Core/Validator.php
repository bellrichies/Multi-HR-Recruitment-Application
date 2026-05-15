<?php

declare(strict_types=1);

namespace App\Core;

class Validator
{
    protected array $errors = [];

    public function validate(array $data, array $rules): array
    {
        $this->errors = [];
        $validated = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $rulesList = is_array($fieldRules) ? $fieldRules : explode('|', (string) $fieldRules);

            foreach ($rulesList as $rule) {
                $this->applyRule($field, $value, (string) $rule);
            }

            if (array_key_exists($field, $data)) {
                $validated[$field] = is_string($value) ? trim($value) : $value;
            }
        }

        if ($this->errors !== []) {
            throw new ValidationException($this->errors);
        }

        return $validated;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);

        match ($name) {
            'required' => $this->required($field, $value),
            'email' => $this->email($field, $value),
            'string' => $this->string($field, $value),
            'integer' => $this->integer($field, $value),
            'numeric' => $this->numeric($field, $value),
            'date' => $this->date($field, $value),
            'min' => $this->min($field, $value, (int) $parameter),
            'max' => $this->max($field, $value, (int) $parameter),
            'in' => $this->in($field, $value, $parameter),
            'nullable' => null,
            default => throw new HttpException("Validation rule {$name} is not supported.", 500),
        };
    }

    private function required(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
        }
    }

    private function email(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a valid email address.');
        }
    }

    private function string(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && ! is_string($value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a string.');
        }
    }

    private function integer(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be an integer.');
        }
    }

    private function numeric(string $field, mixed $value): void
    {
        if ($value !== null && $value !== '' && ! is_numeric($value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be numeric.');
        }
    }

    private function date(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || strtotime($value) === false) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date.');
        }
    }

    private function min(string $field, mixed $value, int $minimum): void
    {
        if (is_string($value) && mb_strlen($value) < $minimum) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least {$minimum} characters.");
        }
    }

    private function max(string $field, mixed $value, int $maximum): void
    {
        if (is_string($value) && mb_strlen($value) > $maximum) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$maximum} characters.");
        }
    }

    private function in(string $field, mixed $value, ?string $parameter): void
    {
        if ($value === null || $value === '' || $parameter === null) {
            return;
        }

        $allowed = explode(',', $parameter);

        if (! in_array((string) $value, $allowed, true)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is invalid.');
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
