<?php
/**
 * Validator.php — Validación de Datos
 * -------------------------------------
 * Clase para validar los datos de entrada de formularios.
 *
 * @package App\Helpers
 */

namespace App\Helpers;

class Validator
{
    /** @var array Errores de validación encontrados */
    private array $errors = [];

    /** @var array Datos a validar */
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Definir reglas de validación
     *
     * @param array $rules Ej: ['email' => 'required|email', 'name' => 'required|min:3']
     */
    public function validate(array $rules): self
    {
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        return $this;
    }

    /**
     * Aplicar una regla de validación individual
     */
    private function applyRule(string $field, mixed $value, string $rule): void
    {
        // Extraer parámetro de la regla (ej: 'min:3' -> param='3')
        $param = null;
        if (str_contains($rule, ':')) {
            [$rule, $param] = explode(':', $rule, 2);
        }

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'Este campo es obligatorio.');
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Debe ser un correo electrónico válido.');
                }
                break;

            case 'min':
                if ($value && mb_strlen($value) < (int)$param) {
                    $this->addError($field, "Debe tener al menos {$param} caracteres.");
                }
                break;

            case 'max':
                if ($value && mb_strlen($value) > (int)$param) {
                    $this->addError($field, "No puede tener más de {$param} caracteres.");
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->addError($field, 'Debe ser un número.');
                }
                break;

            case 'positive':
                if ($value !== null && $value !== '' && (float)$value <= 0) {
                    $this->addError($field, 'Debe ser un número positivo mayor a cero.');
                }
                break;

            case 'date':
                if ($value && !strtotime($value)) {
                    $this->addError($field, 'Debe ser una fecha válida.');
                }
                break;

            case 'in':
                $options = explode(',', $param);
                if ($value && !in_array($value, $options)) {
                    $this->addError($field, 'Valor no permitido.');
                }
                break;

            case 'regex':
                if ($value && !preg_match($param, $value)) {
                    $this->addError($field, 'Formato inválido.');
                }
                break;

            case 'password':
                // Al menos 8 caracteres, una mayúscula, una minúscula, un número
                if ($value && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $value)) {
                    $this->addError($field, 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.');
                }
                break;

            case 'phone':
                if ($value && !preg_match('/^\+?[\d\s\-\(\)]{7,20}$/', $value)) {
                    $this->addError($field, 'Número de teléfono inválido.');
                }
                break;
        }
    }

    /**
     * Agregar un error de validación
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Verificar si la validación pasó (sin errores)
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Verificar si la validación falló
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Obtener todos los errores
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener el primer error de un campo
     */
    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Obtener todos los errores como array plano
     */
    public function allErrors(): array
    {
        $all = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $all[] = $error;
            }
        }
        return $all;
    }

    /**
     * Método estático de conveniencia
     */
    public static function make(array $data, array $rules): self
    {
        return (new self($data))->validate($rules);
    }
}
