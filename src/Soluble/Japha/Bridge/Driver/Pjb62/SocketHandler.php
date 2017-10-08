<?php
/**
 * soluble-japha / PHPJavaBridge driver client.
 *
 * Refactored version of phpjababridge's Java.inc file compatible
 * with php java bridge 6.2
 *
 *
 * @credits   http://php-java-bridge.sourceforge.net/pjb/
 *
 * @see      http://github.com/belgattitude/soluble-japha
 *
 * @author Jost Boekemeier
 * @author Vanvelthem Sébastien (refactoring and fixes from original implementation)
 * @license   MIT
 *
 * The MIT License (MIT)
 * Copyright (c) 2014-2017 Jost Boekemeier
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Soluble\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Driver\Pjb62\Exception\BrokenConnectionException;

class SocketHandler
{
    /**
     * @var Protocol
     */
    public $protocol;

    /**
     * @var EmptyChannel|SocketChannel
     */
    public $channel;

    public function __construct(Protocol $protocol, EmptyChannel $channel)
    {
        $this->protocol = $protocol;
        $this->channel = $channel;
    }

    public function write(string $data): ?int
    {
        return $this->channel->fwrite($data);
    }

    public function fwrite(string $data): ?int
    {
        return $this->write($data);
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function read(int $size): string
    {
        $str = $this->channel->fread($size);
        if ($str === null) {
            $this->shutdownBrokenConnection('Cannot read from socket');
        }

        return $str;
    }

    public function fread(int $size): ?string
    {
        return $this->read($size);
    }

    public function redirect(): void
    {
    }

    public function getKeepAlive(): string
    {
        return $this->channel->getKeepAlive();
    }

    public function keepAlive(): void
    {
        $this->channel->keepAlive();
    }

    public function dieWithBrokenConnection(string $msg = ''): void
    {
        if ($msg === '') {
            $msg = 'Unkown error: please see back end log for detail';
        }

        // Log error
        $client = $this->protocol->getClient();

        $client->getLogger()->critical("[soluble-japha] Broken connection: $msg, check the backend log for details\"  (" . __METHOD__ . ')');

        PjbProxyClient::unregisterInstance();
        throw new BrokenConnectionException("Broken connection: $msg, check the backend log for details");
    }

    public function shutdownBrokenConnection(string $msg = ''): void
    {
        $this->channel->shutdownBrokenConnection();
        $this->dieWithBrokenConnection($msg);
    }
}
