<?php

declare(strict_types=1);

namespace Soluble\Japha\Bridge\Socket;

interface StreamSocketInterface
{
    /**
     * @return resource php socket
     */
    public function getSocket();

    /**
     * Get socket transport identifier ('tcp', 'ssl', 'udp'...).
     */
    public function getTransport(): string;

    /**
     * Return stream address (transport://<ip>:<port>).
     */
    public function getStreamAddress(): string;
}
