<?php
declare(strict_types=1);

namespace App\Auth\IdentityProvider;

use App\Auth\Account;

interface IdentityProvider
{
    public function getSignInFields(array $data): array;

    public function retrieveAccount(array $data): ?Account;

    public function createMagicToken(array $data): ?string;

    public function getSignUpFields(string $token, array $data): array;
}