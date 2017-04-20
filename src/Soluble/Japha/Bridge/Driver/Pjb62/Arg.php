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

use Soluble\Japha\Bridge\Exception\JavaException;

class Arg
{
    /**
     * @var Client
     */
    public $client;
    /**
     * @var string
     */
    public $exception;

    /**
     * @var SimpleFactory
     */
    public $factory;
    public $val;
    /**
     * @var string
     */
    public $signature;

    /**
     * @var string
     */
    public $type;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->factory = $client->simpleFactory;
    }

    public function linkResult(&$val)
    {
        $this->val = &$val;
    }

    public function setResult($val)
    {
        $this->val = &$val;
    }

    /**
     * @param bool $wrap
     *
     * @return JavaException
     */
    public function getResult($wrap)
    {
        $rc = $this->factory->getProxy($this->val, $this->signature, $this->exception, $wrap);

        $factory = $this->factory;
        $this->factory = $this->client->simpleFactory;
        if ($rc instanceof \Soluble\Japha\Bridge\Driver\Pjb62\Exception\JavaException) {
            $factory->checkResult($rc);
        }

        return $rc;
    }

    /**
     * @param SimpleFactory $factory
     */
    public function setFactory(SimpleFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $string
     */
    public function setException($string)
    {
        $this->exception = $string;
    }

    protected function setVoidSignature()
    {
        $this->signature = '@V';
        $key = $this->client->currentCacheKey;
        if ($key && $key[0] != '~') {
            $this->client->currentArgumentsFormat[6] = '3';
            $cacheEntry = new CacheEntry($this->client->currentArgumentsFormat, $this->signature, $this->factory, true);
            $this->client->methodCache[$key] = $cacheEntry;
        }
    }

    /**
     * @param string $signature
     */
    protected function setSignature($signature)
    {
        $this->signature = $signature;
        $key = $this->client->currentCacheKey;
        if ($key && $key[0] != '~') {
            $cacheEntry = new CacheEntry($this->client->currentArgumentsFormat, $this->signature, $this->factory, false);
            $this->client->methodCache[$key] = $cacheEntry;
        }
    }
}
