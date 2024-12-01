<?php

class t_parse extends Model_t
{
    use Saw;

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

    function php($fn, $m) {
        $nice = 'beautifier' == $m;
        $php = PHP::file($fn, $nice ? 4 : 0);
//if ($nice) $php = new PHP($php);
        if (PHP::$warning)
            throw new Error(PHP::$warning);
        if ($nice || 'minifier' == $m)
            return Display::php($php);
        if ('code' == $m)
            return $this->t_token->php_code($php);
        return $this->t_token->token($php, 'nice' == $m);
    }

    function proc($fn, $m) {
        $this->wn_input = unl(file_get_contents($fn));
        $test2 = 'test2' == $m;
        $out = $test2 ? th(['#', '->typ', 'Token', '->bracket'], 'width="99%"') : '';
        $i = 0;
        foreach ($this->tokens() as $t => $y) {
            $tr = in_array($y->typ, [0, 9])
                ? 'style="background:#fdf"'
                : (1 == $y->typ ? 'style="background:#dfd"' : (7 != $y->typ ? 'style="background:#eee"' : ''));
            $out .= $test2 ? td([
                ++$i,
                "$y->typ - " . $this->wn_tokens[$y->typ],
                pre(html(var_export($t, true))),
                [pre(html(var_export($y->bracket, true))), 'width="25%"'],
            ], $tr) : $t . $y->bracket;
        }
        return $test2 ? "$out</table>" : pre(html($out));
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
