<?php

declare(strict_types=1);
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

class ArrayProxy extends IteratorProxy implements \ArrayAccess
{
    /**
     * @param string $idx
     *
     * @return bool
     */
    public function offsetExists($idx): bool
    {
        $ar = [$this, $idx];

        return $this->__client->invokeMethod(0, 'offsetExists', $ar);
    }

    /**
     * @param string|int $idx
     *
     * @return mixed
     */
    public function offsetGet($idx)
    {
        $ar = [$this, $idx];

        return $this->__client->invokeMethod(0, 'offsetGet', $ar);
    }

    /**
     * @param string|int $idx
     * @param mixed      $val
     */
    public function offsetSet($idx, $val)
    {
        $ar = [$this, $idx, $val];

        return $this->__client->invokeMethod(0, 'offsetSet', $ar);
    }

    /**
     * @param string|int $idx
     */
    public function offsetUnset($idx)
    {
        $ar = [$this, $idx];

        return $this->__client->invokeMethod(0, 'offsetUnset', $ar);
    }
}
