<?php

namespace App\Service;

class GenerateurDeMotDePasse
{
    public function genererUnMotDePasse(int $longueur)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=';
        $longueurCharacter = strlen($characters);
        $randomPassword = '';

        for ($i = 0; $i < $longueur; $i++) {
            $randomIndex = random_int(0, $longueurCharacter - 1);
            $randomPassword .= $characters[$randomIndex];
        }
        return $randomPassword;

    }
}