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

class SimpleParser
{
    /**
     *
     * @var integer
     */
    public $SLEN = 256;
    public $handler;
    public $tag;
    public $buf;
    public $len;
    public $s;
    public $type;

    public $BEGIN = 0;
    public $KEY = 1;
    public $VAL = 2;
    public $ENTITY = 3;
    public $VOJD = 5;
    public $END = 6;
    public $level = 0;
    public $eor = 0;
    public $in_dquote;
    public $eot = false;
    public $pos = 0;
    public $c = 0;
    public $i = 0;
    public $i0 = 0;
    public $e;


    /**
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->handler = $client;
        $this->tag = array(new ParserTag(), new ParserTag(), new ParserTag());
        $this->len = $this->SLEN;
        $this->s = str_repeat(" ", $this->SLEN);
        $this->type = $this->VOJD;
    }


    public function RESET()
    {
        $this->type = $this->VOJD;
        $this->level = 0;
        $this->eor = 0;
        $this->in_dquote = false;
        $this->i = 0;
        $this->i0 = 0;
    }

    public function APPEND($c)
    {
        if ($this->i >= $this->len - 1) {
            $this->s = str_repeat($this->s, 2);
            $this->len*=2;
        }
        $this->s[$this->i++] = $c;
    }

    public function CALL_BEGIN()
    {
        $pt = &$this->tag[1]->strings;
        $st = &$this->tag[2]->strings;
        $t = &$this->tag[0]->strings[0];
        $name = $t->string[$t->off];
        $n = $this->tag[2]->n;
        $ar = array();
        for ($i = 0; $i < $n; $i++) {
            $ar[$pt[$i]->getString()] = $st[$i]->getString();
        }
        $this->handler->begin($name, $ar);
    }

    public function CALL_END()
    {
        $t = &$this->tag[0]->strings[0];
        $name = $t->string[$t->off];
        $this->handler->end($name);
    }

    public function PUSH($t)
    {
        $str = &$this->tag[$t]->strings;
        $n = &$this->tag[$t]->n;
        $this->s[$this->i] = '|';
        if (!isset($str[$n])) {
            $h = $this->handler;
            $str[$n] = $h->createParserString();
        }
        $str[$n]->string = &$this->s;
        $str[$n]->off = $this->i0;
        $str[$n]->length = $this->i - $this->i0;
        ++$this->tag[$t]->n;
        $this->APPEND('|');
        $this->i0 = $this->i;
    }

    public function parse()
    {
        $java_recv_size = $this->handler->getParam('JAVA_RECV_SIZE');
        while ($this->eor == 0) {
            if ($this->c >= $this->pos) {
                $this->buf = $this->handler->read($java_recv_size);
                if (is_null($this->buf) || strlen($this->buf) == 0) {
                    $this->handler->protocol->handler->shutdownBrokenConnection("protocol error. Check the back end log for OutOfMemoryErrors.");
                }
                $this->pos = strlen($this->buf);
                if ($this->pos == 0) {
                    break;
                }
                $this->c = 0;
            }
            switch (($ch = $this->buf[$this->c])) {
                case '<':
                    if ($this->in_dquote) {
                        $this->APPEND($ch);
                        break;
                    }
                    $this->level+=1;
                    $this->type = $this->BEGIN;
                    break;
                case '\t':
                case '\f':
                case '\n':
                case '\r':
                case ' ':
                    if ($this->in_dquote) {
                        $this->APPEND($ch);
                        break;
                    }
                    if ($this->type == $this->BEGIN) {
                        $this->PUSH($this->type);
                        $this->type = $this->KEY;
                    }
                    break;
                case '=':
                    if ($this->in_dquote) {
                        $this->APPEND($ch);
                        break;
                    }
                    $this->PUSH($this->type);
                    $this->type = $this->VAL;
                    break;
                case '/':
                    if ($this->in_dquote) {
                        $this->APPEND($ch);
                        break;
                    }
                    if ($this->type == $this->BEGIN) {
                        $this->type = $this->END;
                        $this->level-=1;
                    }
                    $this->level-=1;
                    $this->eot = true;
                    break;
                case '>':
                    if ($this->in_dquote) {
                        $this->APPEND($ch);
                        break;
                    }
                    if ($this->type == $this->END) {
                        $this->PUSH($this->BEGIN);
                        $this->CALL_END();
                    } else {
                        if ($this->type == $this->VAL) {
                            $this->PUSH($this->type);
                        }
                        $this->CALL_BEGIN();
                    }
                    $this->tag[0]->n = $this->tag[1]->n = $this->tag[2]->n = 0;
                    $this->i0 = $this->i = 0;
                    $this->type = $this->VOJD;
                    if ($this->level == 0) {
                        $this->eor = 1;
                    }
                    break;
                case ';':
                    if ($this->type == $this->ENTITY) {
                        switch ($this->s[$this->e + 1]) {
                            case 'l':
                                $this->s[$this->e] = '<';
                                $this->i = $this->e + 1;
                                break;
                            case 'g':
                                $this->s[$this->e] = '>';
                                $this->i = $this->e + 1;
                                break;
                            case 'a':
                                $this->s[$this->e] = ($this->s[$this->e + 2] == 'm' ? '&' : '\'');
                                $this->i = $this->e + 1;
                                break;
                            case 'q':
                                $this->s[$this->e] = '"';
                                $this->i = $this->e + 1;
                                break;
                            default:
                                $this->APPEND($ch);
                        }
                        $this->type = $this->VAL;
                    } else {
                        $this->APPEND($ch);
                    }
                    break;
                case '&':
                    $this->type = $this->ENTITY;
                    $this->e = $this->i;
                    $this->APPEND($ch);
                    break;
                case '"':
                    $this->in_dquote = !$this->in_dquote;
                    if (!$this->in_dquote && $this->type == $this->VAL) {
                        $this->PUSH($this->type);
                        $this->type = $this->KEY;
                    }
                    break;
                default:
                    $this->APPEND($ch);
            }
            $this->c+=1;
        }
        $this->RESET();
    }

    public function getData($str)
    {
        return $str;
    }

    public function parserError()
    {
        $this->handler->protocol->handler->shutdownBrokenConnection(
            sprintf("protocol error: %s. Check the back end log for details.", $this->s)
        );
    }
}
