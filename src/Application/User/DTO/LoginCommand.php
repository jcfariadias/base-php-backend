<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class LoginCommand
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Please provide a valid email address')]
        private string $email,
        
        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 6, minMessage: 'Password must be at least 6 characters long')]
        private string $password
    ) {}
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }
}