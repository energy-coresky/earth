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
        $cnt = function ($ary) use ($php) {
            $s = isset($ary[0]) ? "," . array_shift($ary) : '';
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
            $out .= td([
                ++$i . ' . ' . ($y->line ?: $php->char_line($y->i)) . " . $y->i",
                html($prev),
                [$tok, 'style="background:#' . $color . ';font-family:monospace;text-align:center"'],
                $y->len,
                $y->cnt ? $cnt($y->cnt) : '.',
                $y->reason ? token_name($y->reason) : '.',
                $y->close ?: '.',
            ]);
        }
        return "$out</table>Tokens total: $php->count";
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
        Display::scheme('z_php');
        $x = Display::xdata('');
        $x->len = 0;
        return Display::table(explode("\n", $out), $x, false);
    }
}
