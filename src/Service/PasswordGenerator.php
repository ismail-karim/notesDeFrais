<?php


namespace App\Service;


class PasswordGenerator
{
    public function generateRandomStrongPassword(int $lenght): string
    {
        $uppercaseLetters = $this->generateCharactersWhithCharCodeRange([65, 90]);

        $lowercaseLetters =  $this->generateCharactersWhithCharCodeRange([97, 122]);

        $numbers = $this->generateCharactersWhithCharCodeRange([48, 57]);

        $symbols = $this->generateCharactersWhithCharCodeRange([33, 47, 58, 64, 91, 96, 123, 126]);

        $allCharacters = array_merge($uppercaseLetters, $lowercaseLetters, $numbers, $symbols);

        $isArrayShuffled = shuffle($allCharacters);

        if(!$isArrayShuffled){
            throw new \LogicException("La generation d' mot de passe aléatoir à échoué");
        }

        return implode('',array_slice($allCharacters, 0, $lenght));

    }

    private function generateCharactersWhithCharCodeRange(array $range): array
    {
        if(count($range) === 2){
            return range(chr($range[0]), chr($range[1]));
        }
        else{

            return array_merge(...array_map(fn($range) => range(chr($range[0]), chr($range[1])), array_chunk($range, 2) ));

        }
    }
}