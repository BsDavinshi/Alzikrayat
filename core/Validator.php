<?php
declare(strict_types=1);

final class Validator
{
    public const NAME_PATTERN = '/^\p{L}+(?: \p{L}+)*$/u';

    public const EMAIL_PATTERN = '/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9](?:[A-Za-z0-9\-]*[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9\-]*[A-Za-z0-9])?)*\.[A-Za-z]{2,}$/';

    private array $data;

    private array $errors = [];

    private array $labels;

    public function __construct(array $data, array $labels = [])
    {
        $this->data = $data;
        $this->labels = $labels;
    }

    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            foreach (explode('|', $ruleString) as $rule) {
                [$ruleName, $ruleParameter] = array_pad(explode(':', $rule, 2), 2, null);

                if ($ruleName !== 'required' && ($value === null || $value === '')) {
                    continue;
                }

                $errorMessage = $this->check($field, (string) $ruleName, $value, $ruleParameter);
                if ($errorMessage !== null) {
                    $this->errors[$field] = $errorMessage;
                    break;
                }
            }
        }

        return $this->errors === [];
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function check(string $field, string $ruleName, mixed $value, ?string $ruleParameter): ?string
    {
        $label = $this->labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
        $stringValue = is_scalar($value) ? (string) $value : '';

        return match ($ruleName) {
            'required' => ($value === null || $value === '' || $value === []) ? "{$label} is required." : null,
            'max' => mb_strlen($stringValue) > (int) $ruleParameter
                ? "{$label} may not be longer than {$ruleParameter} characters." : null,
            'min' => mb_strlen($stringValue) < (int) $ruleParameter
                ? "{$label} must be at least {$ruleParameter} characters." : null,
            'name' => preg_match(self::NAME_PATTERN, $stringValue) !== 1
                ? "{$label} may contain letters only." : null,
            'email' => (preg_match(self::EMAIL_PATTERN, $stringValue) !== 1 || filter_var($stringValue, FILTER_VALIDATE_EMAIL) === false)
                ? "{$label} must be a valid email address." : null,
            'strongPassword' => (preg_match('/[A-Za-z]/', $stringValue) !== 1 || preg_match('/\d/', $stringValue) !== 1)
                ? "{$label} must contain at least one letter and one number." : null,
            'matches' => $stringValue !== (string) ($this->data[(string) $ruleParameter] ?? '')
                ? "{$label} does not match." : null,
            'integer' => filter_var($stringValue, FILTER_VALIDATE_INT) === false
                ? "{$label} must be a whole number." : null,
            'in' => !in_array($stringValue, explode(',', (string) $ruleParameter), true)
                ? "{$label} has an invalid value." : null,
            default => throw new InvalidArgumentException("Unknown validation rule '{$ruleName}' on field '{$field}'."),
        };
    }
}
