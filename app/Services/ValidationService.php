<?php

namespace App\Services;

/**
 * Validation Service
 * 
 * Follows Single Responsibility Principle (SRP) - SOLID
 * Responsible only for validation logic
 */
class ValidationService
{
    /**
     * Validate email format
     * 
     * @param string $email
     * @return bool
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate CNPJ format
     * 
     * @param string $cnpj
     * @return bool
     */
    public function validateCNPJ(string $cnpj): bool
    {
        // Remove non-numeric characters
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        // Check if has 14 digits
        if (strlen($cnpj) !== 14) {
            return false;
        }
        
        // Check for known invalid CNPJs
        $invalidCNPJs = [
            '00000000000000', '11111111111111', '22222222222222',
            '33333333333333', '44444444444444', '55555555555555',
            '66666666666666', '77777777777777', '88888888888888',
            '99999999999999'
        ];
        
        if (in_array($cnpj, $invalidCNPJs)) {
            return false;
        }
        
        // Validate check digits
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if (intval($cnpj[12]) !== $digit1) {
            return false;
        }
        
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return intval($cnpj[13]) === $digit2;
    }

    /**
     * Validate CPF format
     * 
     * @param string $cpf
     * @return bool
     */
    public function validateCPF(string $cpf): bool
    {
        // Remove non-numeric characters
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Check if has 11 digits
        if (strlen($cpf) !== 11) {
            return false;
        }
        
        // Check for known invalid CPFs
        $invalidCPFs = [
            '00000000000', '11111111111', '22222222222',
            '33333333333', '44444444444', '55555555555',
            '66666666666', '77777777777', '88888888888',
            '99999999999'
        ];
        
        if (in_array($cpf, $invalidCPFs)) {
            return false;
        }
        
        // Validate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if (intval($cpf[9]) !== $digit1) {
            return false;
        }
        
        // Validate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return intval($cpf[10]) === $digit2;
    }

    /**
     * Validate phone number format
     * 
     * @param string $phone
     * @return bool
     */
    public function validatePhone(string $phone): bool
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if has 10 or 11 digits (with area code)
        $length = strlen($phone);
        
        if ($length < 10 || $length > 11) {
            return false;
        }
        
        // Check area code (first two digits)
        $areaCode = substr($phone, 0, 2);
        $validAreaCodes = [
            '11', '12', '13', '14', '15', '16', '17', '18', '19', // SP
            '21', '22', '24', // RJ
            '27', '28', // ES
            '31', '32', '33', '34', '35', '37', '38', // MG
            '41', '42', '43', '44', '45', '46', // PR
            '47', '48', '49', // SC
            '51', '53', '54', '55', // RS
            '61', // DF
            '62', '64', // GO
            '63', // TO
            '65', '66', // MT
            '67', // MS
            '68', // AC
            '69', // RO
            '71', '73', '74', '75', '77', // BA
            '79', // SE
            '81', '87', // PE
            '82', // AL
            '83', // PB
            '84', // RN
            '85', '88', // CE
            '86', '89', // PI
            '91', '93', '94', // PA
            '92', '97', // AM
            '95', // RR
            '96', // AP
            '98', '99' // MA
        ];
        
        return in_array($areaCode, $validAreaCodes);
    }

    /**
     * Validate required field
     * 
     * @param mixed $value
     * @return bool
     */
    public function validateRequired($value): bool
    {
        if (is_null($value)) {
            return false;
        }
        
        if (is_string($value) && trim($value) === '') {
            return false;
        }
        
        if (is_array($value) && empty($value)) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate minimum length
     * 
     * @param string $value
     * @param int $minLength
     * @return bool
     */
    public function validateMinLength(string $value, int $minLength): bool
    {
        return strlen($value) >= $minLength;
    }

    /**
     * Validate maximum length
     * 
     * @param string $value
     * @param int $maxLength
     * @return bool
     */
    public function validateMaxLength(string $value, int $maxLength): bool
    {
        return strlen($value) <= $maxLength;
    }

    /**
     * Validate numeric value
     * 
     * @param mixed $value
     * @return bool
     */
    public function validateNumeric($value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validate minimum value
     * 
     * @param numeric $value
     * @param numeric $min
     * @return bool
     */
    public function validateMin($value, $min): bool
    {
        return $value >= $min;
    }

    /**
     * Validate maximum value
     * 
     * @param numeric $value
     * @param numeric $max
     * @return bool
     */
    public function validateMax($value, $max): bool
    {
        return $value <= $max;
    }

    /**
     * Validate value is in array
     * 
     * @param mixed $value
     * @param array $options
     * @return bool
     */
    public function validateIn($value, array $options): bool
    {
        return in_array($value, $options);
    }

    /**
     * Validate URL format
     * 
     * @param string $url
     * @return bool
     */
    public function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate date format
     * 
     * @param string $date
     * @param string $format
     * @return bool
     */
    public function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        return $dateTime && $dateTime->format($format) === $date;
    }

    /**
     * Validate password strength
     * 
     * @param string $password
     * @param int $minLength
     * @return array
     */
    public function validatePasswordStrength(string $password, int $minLength = 8): array
    {
        $errors = [];
        
        if (strlen($password) < $minLength) {
            $errors[] = "A senha deve ter pelo menos {$minLength} caracteres";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos uma letra minúscula';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos uma letra maiúscula';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos um número';
        }
        
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos um caractere especial';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}