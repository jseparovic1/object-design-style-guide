<?php

declare(strict_types=1);

class Credentials
{
    private string $userName;
    private string $password;

    public function __construct(string $userName, string $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    public function userName(): string
    {
        return $this->userName;
    }

    public function password(): string
    {
        return $this->password;
    }
}
