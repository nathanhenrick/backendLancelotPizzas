<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LogoDataUrl implements ValidationRule
{
    private const TIPOS = [
        'png'  => 'image/png',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    ];

    public function __construct(private ?string $atual = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '' || ($this->atual !== null && $value === $this->atual)) {
            return;
        }

        if (! is_string($value) || ! preg_match('#^data:image/(png|jpeg|webp|gif);base64,([A-Za-z0-9+/]+={0,2})$#', $value, $m)) {
            $fail('A logo precisa ser uma imagem PNG, JPEG, WebP ou GIF.');
            return;
        }

        $binario = base64_decode($m[2], true);
        $info = $binario === false ? false : @getimagesizefromstring($binario);

        if ($info === false || ($info['mime'] ?? null) !== self::TIPOS[$m[1]]) {
            $fail('A logo não é uma imagem válida.');
        }
    }
}
