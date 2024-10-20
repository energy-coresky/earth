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

    function php_code($fn, $php) {
        $tok = $php->tok;
        $i = 0;
        $str = function ($next) use (&$i, &$tok) {
            for ($out = ''; $i < $next; $i++) {
                [$t, $s] = is_array($tok[$i]) ? $tok[$i] : [0, $tok[$i]];
                $out .= in_array($t, [T_COMMENT, T_DOC_COMMENT]) ? tag($s, '', 'y') : html($s);
            }
            return $out;
        };
        $out = '';
        foreach ($php->rank() as $prev => $y) {
            if (!$y->new)
                break;
            $html = $str($y->new->i);
            if ($y->rank) {
                $title = $php->get_real($y);
                $out .= tag($html, 'title="' . $title . '"', $y->is_def ? 'r' : 'g');
            } else {
                $out .= $html;
            }
        }
        $out .= $str($php->count);
        //$out = html(file_get_contents($fn));
        echo pre($out);
    }

    function php($fn, $m) {
        $m && SKY::w('last_php_m', $m);
        $m or $m = SKY::w('last_php_m');
        $php = PHP::file($fn, 'minifier' == $m ? 0 : 4);
        if ($php->syntax_fail)
            throw new Error($php->syntax_fail);
        if ('minifier' == $m || 'beautifier' == $m)
            return print pre(html($php));
        if ('code' == $m)
            return $this->php_code($fn, $php);
        $out = '';
        foreach ($php->rank() as $prev => $y) {
            if (T_STRING == $y->tok) {
                $real = in_array($y->rank, ['METHOD', 'CLASS-CONST']) ? $y->str
                    : ($y->rank ? $php->get_real($y) : '');
                $s = $php->pos . ".$php->curly $prev $y->line " . token_name($y->tok) . " $real";
                $s .= " ------------------- " . (is_int($y->rank) ? strtolower(token_name($y->rank)) : $y->rank);
                if ($y->open)
                    $s .= $php->str($y->open, $php->get_close($y));
                $out .= "===================== \n$s\n";
            } #else { $out .= $php->curly . " == $y->str\n"; }
        }
        $out = var_export($php->top, true) .
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
