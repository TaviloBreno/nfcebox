<?php

namespace App\Traits;

trait DocumentHelper
{
    /**
     * Remove máscara de CPF/CNPJ
     */
    public function removeDocumentMask(string $document): string
    {
        return preg_replace('/[^0-9]/', '', $document);
    }

    /**
     * Remove máscara de telefone
     */
    public function removePhoneMask(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Remove máscara de CEP
     */
    public function removeCepMask(string $cep): string
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }

    /**
     * Valida CPF
     */
    public function isValidCpf(string $cpf): bool
    {
        $cpf = $this->removeDocumentMask($cpf);
        
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        // Calcula primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if (intval($cpf[9]) != $digit1) {
            return false;
        }
        
        // Calcula segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return intval($cpf[10]) == $digit2;
    }

    /**
     * Valida CNPJ
     */
    public function isValidCnpj(string $cnpj): bool
    {
        $cnpj = $this->removeDocumentMask($cnpj);
        
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }
        
        // Calcula primeiro dígito verificador
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights1[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        
        if (intval($cnpj[12]) != $digit1) {
            return false;
        }
        
        // Calcula segundo dígito verificador
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights2[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;
        
        return intval($cnpj[13]) == $digit2;
    }

    /**
     * Valida se é CPF ou CNPJ válido
     */
    public function isValidDocument(string $document): bool
    {
        $cleanDocument = $this->removeDocumentMask($document);
        
        if (strlen($cleanDocument) == 11) {
            return $this->isValidCpf($document);
        } elseif (strlen($cleanDocument) == 14) {
            return $this->isValidCnpj($document);
        }
        
        return false;
    }

    /**
     * Normaliza dados do request removendo máscaras
     */
    public function normalizeRequestData(array $data): array
    {
        if (isset($data['document'])) {
            $data['document'] = $this->removeDocumentMask($data['document']);
        }
        
        if (isset($data['phone'])) {
            $data['phone'] = $this->removePhoneMask($data['phone']);
        }
        
        if (isset($data['zip_code'])) {
            $data['zip_code'] = $this->removeCepMask($data['zip_code']);
        }
        
        return $data;
    }
}