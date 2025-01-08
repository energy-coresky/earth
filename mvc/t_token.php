<?php

class t_token extends Model_t
{
    function token($php, $nice) {
        if ($nice)
            return $this->token_nice($php);
        $out = th(['Line', '->pos', '->curly', 'Prev', 'Name', 'Rank', '()'], 'width="99%"');
        foreach ($php->rank() as $prev => $y) {
            $ns = '' === $php->ns ? '' : "$php->ns\\";
            if (T_STRING == $y->tok) {
                if ($y->is_def) {
                    $real = in_array($y->rank, ['NAMESPACE', 'CLASS-CONST', 'METHOD']) ? $y->str : $ns . $y->str;
                } else {
                    $real = in_array($y->rank, [T_VAR, T_FN, T_LIST], true) ? $y->str : ($y->rank ? $php->get_real($y) : '');
                }
                $open = $y->open ? $php->str($y->open, $php->get_close($y)) : '';
                $out .= td([
                    $y->line,
                    $php->pos, $php->curly,
                    $prev < 256 ? chr($prev) : token_name($prev),
                    [$real, 'style="background:#eee"'], // token_name($y->tok)
                    is_int($y->rank) ? strtolower(token_name($y->rank)) : $y->rank,
                    mb_strlen($open) > 100 ? html(mb_substr($open, 0, 100)) . ' ...' : html($open),
                ]);
            }
        }
        return pre(html(var_export($php->head, true))) . "$out</table>";
    }

    function gen($php) {
        $php->nice(); # step 1
        $prev = '';
        for ($y = $php->tok(); $y; $y = $new) {
            $new = $php->tok($y->i + 1);
            $php->int_bracket($y);
            yield $prev => $y;
            in_array($y->tok, [T_COMMENT, T_DOC_COMMENT, T_WHITESPACE]) or $prev = $y->str;
        }
    }

    function token_nice($php) {
        $out = th(['# / Line / ->i', 'Prev', 'Token', '->len',  '->cnt', '->reason', '->close'], 'width="99%"');
        $i = 0;
        $cnt = function ($ary, &$len2) use ($php) {
            $s = isset($ary[0]) ? "," . array_shift($ary) : '';
            if (isset($ary[-1])) {
                $len2 = ", ($ary[-1])";
                unset($ary[-1]);
            }
            if (!$ary)
                return $s;
            foreach ($ary as $k => $v)
                $s .= '_' . (is_array($php->tok[$k]) ? $php->tok[$k][1] : $php->tok[$k]);
            return html($s);
        };
        foreach ($this->gen($php) as $prev => $y) {
            if (!$y->len)
                continue;
            $tok = $y->tok ? token_name($y->tok) : ('{' == $y->str ? 'curly_open' : $y->str);
            $color = $y->close > 1 ? 'eee' : 'ff9';
            $len2 = '';
            $chars = $y->cnt ? $cnt($y->cnt, $len2) : '.';
            $out .= td([
                ++$i . ' . ' . ($y->line ?: $php->char_line($y->i)) . " . $y->i",
                html($prev),
                [$tok, 'style="background:#' . $color . ';font-family:monospace;text-align:center"'],
                $y->len . $len2,
                $chars,
                $y->reason ? token_name($y->reason) : '.',
                $y->close ?: '.',
            ]);
        }
        return "$out</table>Tokens total: $php->count";
    }

    function token_md($str) {
        $md = new MD($str);
        $out = th(['#', 'tok', 'html', /*'space',*/ 'value'], 'width="99%"');
        $replace = [' ' => '·', "\n" => '→', "\t" => '→'];
        $list = yml('- @inc(yml.markdown) mvc/_parse.jet');
        foreach ($md->parse() as $i => $ary) {
            [$tok, $t, $html] = $ary + [2 => false];
            $out .= td([
                ++$i . ".$tok",
                $list[0][$tok < 100 ? $tok : $tok - 100],
                false === $html ? "<r>...</r>" : html($html),
                //"\n" == $t[0] || '' === trim($t) ? strtr($t, " \n\t", 'snt') : '',
                tag(strtr(html($t), $replace), '', 'code'),
            ]);
        }
        return $out . Show::php(PHP::ary($list, true) . ";\n");
    }

    function token_js($str) {
        $js = new JS($str);
        $tok = fn($tok) => $tok < 1000 ? token_name($tok) : 'T_KEYWORD';
        $out = th(['# / Line / ->i', '->pv', '->tok', 'Value'], 'width="99%"');
        $i = 0;
        foreach ($js->tokens() as $t => $y) {
            $out .= td([
                ++$i . '.',
                html($y->pv),
                $y->tok ? $tok($y->tok) : '',
                T_WHITESPACE == $y->tok ? var_export($t, true) : html($t),
            ]);
        }
        return $out;
    }

    function token_xml($xml, $dump) {
        if ($dump) {
            $xml->parse();
            ob_start();
            $xml->dump();
            return pre(html(ob_get_clean()));
        }
        $out = th(['###', 'ModeIn', 'ModeOut', 'Tokens'], 'width="99%"');
        $i = 0;
        $attr = [];
        foreach ($xml->tokens() as $t => $y) {
            $mode = $y->mode;
            if ($z = $y->end) { # from <!-- or <![CDATA[
                $y->find = $y->end;
            } elseif (in_array($y->found, ['-->', ']]>'])) {
                $t .= $y->find ? '' : $y->found;
                $y->len += $y->find ? 0 : 3; # chars move
            } elseif ('attr' == $y->mode) {
                if ('>' != $t) {
                    ++$i;
                    $attr or $attr[] = $i;
                    $attr[] = $y->space ? "<r>s</r>" : html($t);
                    continue;
                }
                if (in_array($y->tag, ['script', 'style']))
                    $y->find = "</$y->tag>";
            } elseif ($z = 'open' == $y->mode) { # sample: <tag
                $y->tag = substr($t, 1);
                $y->mode = 'attr';
            }
            $z && !$y->end or $y->mode = 'txt';
            if ($attr)
                $out .= td([array_shift($attr) . "..$i", 'attr', 'attr', implode(" <g>|</g> ", $attr)]);
            $attr = [];
            $out .= td([++$i, $mode, $y->mode, '' === trim($t) ? tag('space' . strlen($t), '', 'r') : html($t)]);
        }
        return $out;
    }

    function php_code($php) {
        $tok = $php->tok;
        $str = function ($y) use (&$tok, $php) {
            static $i = 0;
            for ($out = ''; strlen($out) < strlen($y->str); $i++)
                $out .= is_array($tok[$i]) ? $tok[$i][1] : $tok[$i];
            yield html($out);
            if ($i < $y->lst) {
                $y->rank = false;
                for ($out = ''; $i < $y->lst; $i++) {
                    [$t, $s] = is_array($tok[$i]) ? $tok[$i] : [0, $tok[$i]];
                    $gray = in_array($t, [T_COMMENT, T_DOC_COMMENT, T_INLINE_HTML]);
                    $out .= $gray ? tag(html($s), '', 'y') : html($s);
                }
                yield $out;
            }
        };
        $use = fn($y) => in_array($y->rank, [T_CONST, T_CLASS, T_FUNCTION]);
        $out = '';
        $pos = -1;
        foreach ($php->rank() as $prev => $y) {
            $y->lst = $y->new ? $y->new->i : count($tok);
            foreach ($str($y) as $html) if ($y->rank) {
                $title = $php->get_real($y);
                $color = $y->is_def ? 'r' : ($use($y) ? 'z' : (T_LIST == $y->rank ? 'm' : 'g'));
                $out .= tag($html, 'title="' . $title . '"', $color);
                'CLASS' != $y->rank or $pos = $php->pos;
            } else {
                if (T_VARIABLE == $y->tok && $php->pos == $pos)
                    $html = tag($html, '', 'r');
                $out .= $html;
                $php->pos == PHP::_CLASS or $pos = -1;
            }
        }
        return Show::lines($out);
    }
}
