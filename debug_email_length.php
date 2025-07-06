<?php

require_once 'vendor/autoload.php';

use App\Domain\User\ValueObject\Email;

// Debug the long email test
$longLocalPart = str_repeat('a', 240);
$longEmail = $longLocalPart . '@example.com';

echo "Long email length: " . strlen($longEmail) . "\n";
echo "Long email: $longEmail\n";

echo "Testing filter_var result: ";
var_dump(filter_var($longEmail, FILTER_VALIDATE_EMAIL));

try {
    Email::fromString($longEmail);
    echo "ERROR: Should have thrown exception\n";
} catch (Exception $e) {
    echo "Exception thrown: " . $e->getMessage() . "\n";
}