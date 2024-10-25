<?php

class t_parse extends Model_t
{
    private $a = [
        'php' => DIR_S . '/sky.php',
        'js' => DIR_S . '/assets/sky.js',
        'css' => DIR_S . '/assets/sky.css',
        'xml' => DIR_S . '/etc/highlight_jet/npp/jet.xml',
        'yml' => 'main/config.yaml',
        'zml' => 'var/app.sky',
    ];

    function def($i) {
        MVC::body("parse.$this->_2");
        $php_m = ['code', 'tokens', 'minifier', 'beautifier'];
        return [
            'php_m' => $php_m,
            'default' => $this->a[$i],
            'parse_m' => array_keys($this->a),
        ];
    }

    function run($fn) {
        SKY::w($this->_2 . '_file', $fn);
        if (!is_file($fn))
            return $this->dev->error("File `$fn` not found");
        return $this->{$this->_2}($_POST['fn'], $_POST['m']);
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
                    $html = tag($html, 'title="' . '' . '"', 'r');
                $out .= $html;
                $php->pos == PHP::_CLASS or $pos = -1;
            }
        }
        echo pre($out);
    }

    function php($fn, $m) {
        $m && SKY::w('last_php_m', $m);
        $m or $m = SKY::w('last_php_m');
        $php = PHP::file($fn, 'minifier' == $m ? 0 : 4);
        if ($php->parse_error)
            throw new Error($php->parse_error);
        if ('minifier' == $m || 'beautifier' == $m)
            return print pre(html($php));
        if ('code' == $m)
            return $this->php_code($php);
        $out = '';
        foreach ($php->rank() as $prev => $y) {
            $ns = '' === $php->ns ? '' : "$php->ns\\";
            if (T_STRING == $y->tok) {
                if ($y->is_def) {
                    $real = in_array($y->rank, ['NAMESPACE', 'CLASS-CONST', 'METHOD']) ? $y->str : $ns . $y->str;
                } else {
                    $real = in_array($y->rank, [T_VAR, T_FN], true) ? $y->str : ($y->rank ? $php->get_real($y) : '');
                }
                $s = "$php->pos.$php->curly $prev $y->line " . token_name($y->tok) . " $real";
                $s .= " ------------------- " . (is_int($y->rank) ? strtolower(token_name($y->rank)) : $y->rank);
                if ($y->open)
                    $s .= $php->str($y->open, $php->get_close($y));
                $out .= "===================== \n$s\n";
            } #else { $out .= $php->curly . " == $y->str\n"; }
        }
        $out = var_export($php->head, true) .
            //var_export($php->_tokens_def, true) . 
            $out;
        echo pre(html($out));
    }

    function js($fn) {
        echo 1;
    }

    function css($fn) {
    }

    function xml($fn) {
    }

    function yml($fn) {
    }

    function zml($fn) {
        $zml = new ZML($fn);
        echo Debug::out($zml->bang);
        $ary = [];
        foreach ($zml->read() as $pos => $y)
            $ary[$pos] = trim($y->line);
        echo Debug::out($ary);
    }
}
