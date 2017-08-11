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

use Soluble\Japha\Bridge\Driver\Pjb62\Exception\InternalException;
use Soluble\Japha\Bridge\Driver\Pjb62\Utils\HelperFunctions;

class ThrowExceptionProxyFactory extends ExceptionProxyFactory
{
    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    /**
     * @return Exception\InternalException
     */
    public function getProxy($result, $signature, $exception, $wrap): InternalException
    {
        $proxy = static::create($result, $signature);

        return new InternalException($proxy, $exception);
    }

    /**
     * @param Exception\JavaException $result
     *
     * @throws Exception\JavaException
     */
    public function checkResult(Exception\JavaException $result): void
    {
        if (PjbProxyClient::getInstance()->getOption('java_prefer_values') || ($result->__hasDeclaredExceptions === 'T')) {
            throw $result;
        } else {
            $msg = 'Unchecked exception detected: ' . HelperFunctions::java_truncate($result->__toString());
            // Log error
            $this->client->getLogger()->critical("[soluble-japha] $msg (" . __METHOD__ . ')');
            trigger_error($msg, E_USER_WARNING);
        }
    }
}
