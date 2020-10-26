<?php


namespace App\Security\Exception;


use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class NotVerifiedEmailException extends CustomUserMessageAccountStatusException
{

    public function __construct(
        string $message = 'Ce compte ne semble pas posséder une email vérifié',
        array $messageData = [],
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $messageData, $code, $previous);
    }
}