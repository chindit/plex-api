<?php

namespace Chindit\PlexApi\Model;

final class Connection
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly bool $isLocal,
        public readonly bool $isRelay,
        public readonly bool $isIpv6
    )
    {

    }
}