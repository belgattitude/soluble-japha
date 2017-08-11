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

class NativeParser implements ParserInterface
{
    /**
     * @var resource
     */
    protected $parser;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $level;

    /**
     * @var bool
     */
    protected $event;

    /**
     * @var string
     */
    protected $buf;

    /**
     * @var int
     */
    protected $java_recv_size;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->parser = xml_parser_create();
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'begin', 'end');
        xml_parse($this->parser, '<F>');
        $this->level = 0;
        $this->java_recv_size = $client->java_recv_size;
    }

    /**
     * @param resource $parser
     * @param string   $name
     * @param mixed    $param
     */
    protected function begin($parser, $name, $param): void
    {
        $this->event = true;
        switch ($name) {
            case 'X':
            case 'A':
                ++$this->level;
                break;
        }
        $this->client->begin($name, $param);
    }

    /**
     * @param resource $parser
     * @param string   $name
     */
    public function end($parser, $name): void
    {
        $this->client->end($name);
        switch ($name) {
            case 'X':
            case 'A':
                --$this->level;
        }
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function getData(string $str): string
    {
        return base64_decode($str);
    }

    public function parse(): void
    {
        do {
            $this->event = false;
            $this->buf = $this->client->read($this->java_recv_size);
            $len = strlen($this->buf);
            if (!xml_parse($this->parser, $this->buf, $len == 0)) {
                $this->client->protocol->handler->shutdownBrokenConnection(
                    sprintf('protocol error: %s,%s at col %d. Check the back end log for OutOfMemoryErrors.', $this->buf, xml_error_string(xml_get_error_code($this->parser)), xml_get_current_column_number($this->parser))
                );
            }
        } while (!$this->event || $this->level > 0);
    }

    public function parserError(): void
    {
        $this->client->protocol->handler->shutdownBrokenConnection(
            sprintf('protocol error: %s. Check the back end log for details.', $this->buf)
        );
    }
}
