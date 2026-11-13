<?php
declare(strict_types=1);

/**
 * Flowzen - Input Validator & Old Input Preserver
 * Validates request data and maintains validation errors and previous form values.
 */

class Validator {
    private array $data = [];
    private array $errors = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Check if a field is not empty.
     */
    public function required(string $field, string $customMessage = null): self {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val === '') {
            $this->addError($field, $customMessage ?? ucfirst(str_replace('_', ' ', $field)) . ' is required.');
        }
        return $this;
    }

    /**
     * Validate maximum string length.
     */
    public function max(string $field, int $max, string $customMessage = null): self {
        $val = (string)($this->data[$field] ?? '');
        if (mb_strlen($val) > $max) {
            $this->addError($field, $customMessage ?? ucfirst(str_replace('_', ' ', $field)) . " cannot exceed {$max} characters.");
        }
        return $this;
    }

    /**
     * Validate minimum string length.
     */
    public function min(string $field, int $min, string $customMessage = null): self {
        $val = (string)($this->data[$field] ?? '');
        if (mb_strlen($val) < $min && $val !== '') {
            $this->addError($field, $customMessage ?? ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.");
        }
        return $this;
    }

    /**
     * Validate email format.
     */
    public function email(string $field, string $customMessage = null): self {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $customMessage ?? 'Please provide a valid email address.');
        }
        return $this;
    }

    /**
     * Validate that two fields match (e.g. password & confirm_password).
     */
    public function matches(string $field, string $matchField, string $customMessage = null): self {
        $val1 = (string)($this->data[$field] ?? '');
        $val2 = (string)($this->data[$matchField] ?? '');
        if ($val1 !== $val2) {
            $this->addError($matchField, $customMessage ?? ucfirst(str_replace('_', ' ', $matchField)) . ' does not match.');
        }
        return $this;
    }

    /**
     * Validate that value belongs to an allowed list (enum).
     */
    public function in(string $field, array $allowed, string $customMessage = null): self {
        $val = (string)($this->data[$field] ?? '');
        if ($val !== '' && !in_array($val, $allowed, true)) {
            $this->addError($field, $customMessage ?? 'Invalid selection for ' . str_replace('_', ' ', $field) . '.');
        }
        return $this;
    }

    /**
     * Validate date format (YYYY-MM-DD).
     */
    public function date(string $field, string $customMessage = null): self {
        $val = trim((string)($this->data[$field] ?? ''));
        if ($val !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $val);
            if (!$d || $d->format('Y-m-d') !== $val) {
                $this->addError($field, $customMessage ?? 'Please provide a valid date in YYYY-MM-DD format.');
            }
        }
        return $this;
    }

    /**
     * Add an error manually.
     */
    public function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    /**
     * Check if validation passed.
     */
    public function passes(): bool {
        return empty($this->errors);
    }

    /**
     * Check if validation failed.
     */
    public function fails(): bool {
        return !empty($this->errors);
    }

    /**
     * Get all validation errors.
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Get the first error message for a specific field.
     */
    public function getError(string $field): ?string {
        return $this->errors[$field] ?? null;
    }

    /**
     * Flash current errors and inputs to session for redisplay.
     */
    public function flash(): void {
        Session::set('_validation_errors', $this->errors);
        Session::set('_old_input', $this->data);
    }

    /**
     * Retrieve old input with fallback.
     */
    public static function old(string $key, mixed $default = ''): mixed {
        $old = Session::get('_old_input', []);
        return $old[$key] ?? $default;
    }

    /**
     * Clear flashed validation errors and old inputs.
     */
    public static function clearFlashed(): void {
        Session::remove('_validation_errors');
        Session::remove('_old_input');
    }
}

/**
 * Helper function for views to retrieve previously submitted form inputs
 */
if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed {
        $val = Validator::old($key, $default);
        return is_string($val) ? htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $val;
    }
}
