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
        return ['default' => $this->a[$i]];
    }

    function run($fn) {
        SKY::w($this->_2 . '_file', $fn);
        if (!is_file($fn))
            return $this->dev->error("File `$fn` not found");
        MVC::body("parse.$this->_2");
        return $this->{$this->_2}($_POST['fn']);
    }

    function php($fn) {
        $php = PHP::file($fn);
        if ($php->syntax_fail)
            throw new Error($php->syntax_fail);
        $out = '';
        foreach ($php->rank() as $zz => $y) {
            if (T_STRING == $y->tok) {
                $s = $php->pos . ".$php->curly $zz $y->line " . token_name($y->tok) . ' ' . $php->get_real($y);
                $s .= " ------------------- " . (is_int($y->rank) ? strtolower(token_name($y->rank)) : $y->rank);
                if ($y->open)
                    $s .= $php->str($y->open, $php->get_close($y));
                $out .= "===================== \n$s\n";
            } #else { $out .= $php->curly . " == $y->str\n"; }
        }
        $out = var_export($php->use, true) . var_export($php->ns, true) .
            //var_export($php->_tokens_def, true) . 
            $out;
        echo pre(html($out));
    }

    function js($fn) {
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
