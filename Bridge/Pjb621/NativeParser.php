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
namespace Soluble\Japha\Bridge\Pjb621;

class NativeParser
{
    public $parser, $handler;
    public $level, $event;
    public $buf;

    public function __construct($handler)
    {
        $this->handler = $handler;
        $this->parser = xml_parser_create();
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, "begin", "end");
        xml_parse($this->parser, "<F>");
        $this->level = 0;
    }

    public function begin($parser, $name, $param)
    {
        $this->event = true;
        switch ($name) {
            case 'X': case 'A': $this->level+=1;
        }
        $this->handler->begin($name, $param);
    }

    public function end($parser, $name)
    {
        $this->handler->end($name);
        switch ($name) {
            case 'X': case 'A': $this->level-=1;
        }
    }

    public function getData($str)
    {
        return base64_decode($str);
    }

    public function parse()
    {
        do {
            $this->event = false;
            $buf = $this->buf = $this->handler->read(JAVA_RECV_SIZE);
            $len = strlen($buf);
            if (!xml_parse($this->parser, $buf, $len == 0)) {
                $this->handler->protocol->handler->shutdownBrokenConnection(
                    sprintf("protocol error: %s,%s at col %d. Check the back end log for OutOfMemoryErrors.", $buf, xml_error_string(xml_get_error_code($this->parser)), xml_get_current_column_number($this->parser))
                );
            }
        } while (!$this->event || $this->level > 0);
    }

    public function parserError()
    {
        $this->handler->protocol->handler->shutdownBrokenConnection(
            sprintf("protocol error: %s. Check the back end log for details.", $this->buf)
        );
    }
}
