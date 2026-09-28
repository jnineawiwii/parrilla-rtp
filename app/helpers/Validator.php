<?php
class Validator {
    private array $errores = [];

    public function required(string $campo, $valor, string $msg = null): self {
        if (is_null($valor) || trim((string)$valor) === '') {
            $this->errores[$campo] = $msg ?? "El campo $campo es obligatorio.";
        }
        return $this;
    }

    public function fecha(string $campo, $valor): self {
        if ($valor && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            $this->errores[$campo] = "Formato de fecha inválido.";
        }
        return $this;
    }

    public function email(string $campo, $valor): self {
        if ($valor && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->errores[$campo] = "Correo inválido.";
        }
        return $this;
    }

    public function min(string $campo, $valor, int $n): self {
        if (mb_strlen((string)$valor) < $n) {
            $this->errores[$campo] = "Debe tener al menos $n caracteres.";
        }
        return $this;
    }

    public function falla(): bool {
        return !empty($this->errores);
    }

    public function errores(): array {
        return $this->errores;
    }
}