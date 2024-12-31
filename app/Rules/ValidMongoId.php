<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;

class ValidMongoId implements Rule
{
    public function passes($attribute, $value)
    {
        // Vérifie si l'ID est une chaîne valide pour ObjectId
        if (!$this->isValidObjectId($value)) {
            return false;
        }

        // Vérifie si l'ID existe dans la collection "users"
        return DB::collection('users')->where('_id', new ObjectId($value))->exists();
    }

    public function message()
    {
        return 'The :attribute is not a valid ID or does not exist.';
    }

    private function isValidObjectId($value)
    {
        // Vérifie si la chaîne a exactement 24 caractères hexadécimaux
        return is_string($value) && preg_match('/^[0-9a-fA-F]{24}$/', $value);
    }
}
