<?php

class t_parse extends Model_t
{
    use Processor;

    private $a = [
        'php' => DIR_S . '/sky.php',
        'js' => DIR_S . '/assets/sky.js',
        'css' => DIR_S . '/assets/sky.css',
        'xml' => DIR_S . '/etc/highlight_jet/npp/jet.xml',
        'yml' => 'main/config.yaml',
        'zml' => 'var/app.sky',
        'proc' => DIR_S . '/w2/__dev.jet',
    ];

    function a($i) {
        SKY::w('last_parse', $this->_2);
        MVC::body("parse.body");
        return [
            'submenu' => yml("+ @inc(.sub_$this->_2) mvc/_parse.jet"),
            'default' => $this->a[$i],
            'parse_m' => array_keys($this->a),
            'w_sublast' => "w_last_{$this->_2}_m",
        ];
    }

    function j($fn) {
        SKY::w($this->_2 . '_file', $fn);
        if (!is_file($fn))
            return $this->dev->error("File `$fn` not found");
        $history = Vendor::history($this->_2, $fn);
        ($m = $_POST['m']) ? SKY::w("last_{$this->_2}_m", $m) : ($m = SKY::w("last_{$this->_2}_m"));
        json([
            'html' => $this->{$this->_2}($_POST['fn'], $m),
            'history' => view('parse.hist', ['ary' => $history]),
        ]);
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

    function php($fn, $m) {
        $nice = 'beautifier' == $m;
        $php = PHP::file($fn, $nice ? 4 : 0);
//if ($nice) $php = new PHP($php);
        if (PHP::$warning)
            throw new Error(PHP::$warning);
        if ($nice || 'minifier' == $m)
            return Display::php($php);
        if ('code' == $m)
            return $this->php_code($php);
        $out = '';
        foreach ($php->rank() as $prev => $y) {
            $ns = '' === $php->ns ? '' : "$php->ns\\";
            if (T_STRING == $y->tok) {
                if ($y->is_def) {
                    $real = in_array($y->rank, ['NAMESPACE', 'CLASS-CONST', 'METHOD']) ? $y->str : $ns . $y->str;
                } else {
                    $real = in_array($y->rank, [T_VAR, T_FN, T_LIST], true) ? $y->str : ($y->rank ? $php->get_real($y) : '');
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
        return pre(html($out));
    }

    function proc($fn, $m) {
        $out = unl(file_get_contents($fn));
        Display::scheme('z_php');
        $x = Display::xdata('');
        $x->len = 0;
        return Display::table(explode("\n", html($out)), $x, false);
    }

    function js($fn, $m) {
        return '2do';
    }

    function css($fn, $m) {
        return Display::css(file_get_contents($fn));
    }

    function xml($fn, $m) {
        return Display::html(file_get_contents($fn));
    }

    function yml($fn, $m) {
        return Display::yaml(file_get_contents($fn));
    }

    function zml($fn, $m) {
        $zml = new ZML($fn);
        ob_start();
        Debug::out($zml->bang);
        $ary = [];
        foreach ($zml->read() as $pos => $y)
            $ary[$pos] = trim($y->line);
        Debug::out($ary);
        return ob_get_clean();
    }
}
