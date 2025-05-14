<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64File implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) return false;

        $pattern = '/^data:(image\/(jpeg|png)|application\/pdf);base64,[A-Za-z0-9\/+=]+$/';

        return preg_match($pattern, $value) === 1;
    }

    public function message(): string
    {
        return 'File harus berupa gambar (jpeg/png) atau PDF dalam format base64 yang valid.';
    }
}
