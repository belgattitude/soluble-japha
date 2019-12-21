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

use Soluble\Japha\Bridge\Exception\BrokenConnectionException;

abstract class SocketChannel extends EmptyChannel
{
    /**
     * @var resource
     */
    public $peer;

    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    protected $recv_size;

    /**
     * @var int
     */
    protected $send_size;

    /**
     * @param resource $peer
     * @param string   $host
     * @param int      $recv_size
     * @param int      $send_size
     */
    public function __construct($peer, $host, $recv_size, $send_size)
    {
        $this->peer = $peer;
        $this->host = $host;
        $this->recv_size = $recv_size;
        $this->send_size = $send_size;
    }

    /**
     * @throws BrokenConnectionException
     */
    public function fwrite(string $data): ?int
    {
        $written = @fwrite($this->peer, $data);
        if ($written === false) {
            PjbProxyClient::unregisterAndThrowBrokenConnectionException(
                sprintf(
                    'Broken socket communication with the php-java-bridge while reading socket (%s).',
                    __METHOD__
                )
            );
        }

        return $written;
    }

    public function fread(int $size): ?string
    {
        $read = @fread($this->peer, $size);
        if ($read === false) {
            PjbProxyClient::unregisterAndThrowBrokenConnectionException(
                sprintf(
                    'Broken socket communication with the php-java-bridge while reading socket (%s).',
                    __METHOD__
                )
            );
        } else {
            return $read;
        }
    }

    public function shutdownBrokenConnection(?string $msg = ''): void
    {
        if (is_resource($this->peer)) {
            fflush($this->peer);
            fclose($this->peer);
        }
    }
}
