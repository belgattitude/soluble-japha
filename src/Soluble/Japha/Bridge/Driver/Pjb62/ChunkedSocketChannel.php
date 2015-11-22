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

use Soluble\Japha\Bridge\Exception;

class ChunkedSocketChannel extends SocketChannel
{
    public function fwrite($data)
    {
        $len = dechex(strlen($data));
        $res = @fwrite($this->peer, "${len}\r\n${data}\r\n");
        if (!$res) {
            $msg = "Cannot write to socket";
            throw new Exception\RuntimeException($msg);
        }
    }

    public function fread($size)
    {
        $length = hexdec(fgets($this->peer, $this->recv_size));
        $data = "";
        while ($length > 0) {
            $str = fread($this->peer, $length);
            if (feof($this->peer)) {
                return null;
            }
            $length -=strlen($str);
            $data .=$str;
        }
        fgets($this->peer, 3);
        return $data;
    }

    public function keepAlive()
    {
        $this->keepAliveSC();
        $this->checkE();
        fclose($this->peer);
    }
}
