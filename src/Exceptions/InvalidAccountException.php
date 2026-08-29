<?php

namespace Chindit\PlexApi\Exceptions;

class InvalidAccountException extends \InvalidArgumentException
{
    public function __construct(?string $uri = null)
    {
        parent::__construct(sprintf('Unable to connect server at %s', $uri));
    }
}