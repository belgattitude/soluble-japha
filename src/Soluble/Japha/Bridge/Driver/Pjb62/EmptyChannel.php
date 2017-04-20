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

use Soluble\Japha\Bridge\Exception;

class EmptyChannel
{
    /**
     * @var SocketHandler
     */
    protected $handler;

    /**
     * @var string
     */
    private $res;

    /**
     * @var int
     */
    protected $recv_size;

    /**
     * @var int
     */
    protected $send_size;

    /**
     * @param SocketHandler $handler
     * @param int           $recv_size
     * @param int           $send_size
     */
    public function __construct(SocketHandler $handler, $recv_size, $send_size)
    {
        $this->send_size = $send_size;
        $this->recv_size = $recv_size;
        $this->handler = $handler;
    }

    public function shutdownBrokenConnection()
    {
    }

    /**
     * @param string $data
     *
     * @return int
     */
    public function fwrite($data)
    {
        return $this->handler->fwrite($data);
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function fread($size)
    {
        return $this->handler->fread($size);
    }

    public function getKeepAliveA()
    {
        return '<F p="A" />';
    }

    public function getKeepAliveE()
    {
        return '<F p="E" />';
    }

    public function getKeepAlive()
    {
        return $this->getKeepAliveE();
    }

    /**
     * @throws Exception\RuntimeException
     */
    protected function error()
    {
        $msg = 'An unchecked exception occured during script execution. Please check the server log files for details.';
        throw new Exception\RuntimeException($msg);
    }

    public function checkA($peer)
    {
        $val = $this->res[6];
        if ($val != 'A') {
            fclose($peer);
        }
        if ($val != 'A' && $val != 'E') {
            $this->error();
        }
    }

    public function checkE()
    {
        $val = $this->res[6];
        if ($val != 'E') {
            $this->error();
        }
    }

    public function keepAliveS()
    {
        $this->res = $this->fread(10);
    }

    public function keepAliveSC()
    {
        $this->res = $this->fread(10);
        $this->fwrite('');
        $this->fread($this->recv_size);
    }

    public function keepAliveH()
    {
        $this->res = $this->handler->read(10);
    }

    public function keepAlive()
    {
        $this->keepAliveH();
        $this->checkE();
    }
}
