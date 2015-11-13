<?php
/**
 * Soluble Japha / PhpJavaBridge
 *
 * Refactored version of phpjababridge's Java.inc file compatible
 * with php java bridge 6.2.1
 *
 *
 * @credits   http://php-java-bridge.sourceforge.net/pjb/
 *
 * @link      http://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2014 Soluble components
 * @author Vanvelthem Sébastien
 * @license   MIT
 *
 * The MIT License (MIT)
 * Copyright (c) 2014 Vanvelthem Sébastien
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
 *
 */
namespace Soluble\Japha\Bridge\Driver\Pjb62;

class EmptyChannel
{
    protected $handler;
    private $res;
    
    
    /**
     *
     * @var int
     */
    protected $recv_size;
    
    /**
     *
     * @var int
     */
    protected $send_size;

    /**
     * 
     * @param resource $handler
     * @param int $recv_size
     * @param int $send_size
     */
    public function __construct($handler, $recv_size, $send_size)
    {
        $this->recv_size = $recv_size;
        $this->send_size = $send_size;
        $this->handler = $handler;
    }

    public function shutdownBrokenConnection()
    {
    }

    public function fwrite($data)
    {
        return $this->handler->fwrite($data);
    }

    public function fread($size)
    {
        return $this->handler->fread($size);
    }

    public function getKeepAliveA()
    {
        return "<F p=\"A\" />";
    }

    public function getKeepAliveE()
    {
        return "<F p=\"E\" />";
    }

    public function getKeepAlive()
    {
        return $this->getKeepAliveE();
    }

    public function error()
    {
        trigger_error("An unchecked exception occured during script execution. Please check the server log files for details.", E_USER_ERROR);
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
        $this->fwrite("");
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
