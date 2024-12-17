<?php

namespace App\Utils;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationErrorFormatter {
    public static function format(ConstraintViolationListInterface $errors): array {
        $formattedErrors = [];

        foreach ($errors as $error) {
            $formattedErrors[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return $formattedErrors;
    }
}
