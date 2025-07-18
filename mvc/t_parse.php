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
        'saw' => DIR_S . '/w2/__dev.jet',
        'md' => 'README.md',
        'diff' => DIR_S . '/w2/__dev.jet',
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
        $history = Vendor::history($fun = $this->_2, $fn);
        SKY::w($fun . '_file', $fn);
#        $_fn = ($pos = strpos($fn, ':')) ? substr($fn, $pos + 1) : $fn;
        ($m = $_POST['m']) ? SKY::w("last_{$fun}_m", $m) : ($m = SKY::w("last_{$fun}_m"));
#        if ('git' != $m && !is_file(SKY::w('repo') . "/$_fn"))
#            return $this->dev->error("File `$fn` not found");
        if ($_POST['a'])
            SKY::w('as_html', $_POST['a'] - 1);
        json([
            'html' => $this->{$fun}(in_array($fun, ['diff', 'zml']) ? $_POST['fn'] : file_get_contents($_POST['fn']), $m),
            'history' => view('parse.hist', ['ary' => $history]),
        ]);
    }

    function php($src, $m) {
        $nice = 'beautifier' == $m;
        $php = new PHP($src, $nice ? 4 : 0);
//if ($nice) $php = new PHP($php);
        if (PHP::$warning)
            throw new Error(PHP::$warning);
        if ($nice)
            return Show::php($php);
        if ('minifier' == $m)
            return html($php);
        if ('code' == $m)
            return $this->t_token->php_code($php);
        return $this->t_token->token($php, 'nice' == $m);
    }

    function saw($src, $m) {
        $this->wn_input = unl($src);
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

    function js($src, $m) {
        return 'hightlight' == $m ? Show::js($src) : $this->t_token->token_js($src);
    }

    function md($src, $m) {
        switch ($m) {
            case 'hightlight': return Show::md($src);
            case 'dump': return $this->t_token->token_xml(new MD($src), true);
            case 'tok': return $this->t_token->token_md($src);
            case 'raw': return Show::html(Show::doc($src, 'md_raw'));
            case 'html': return SKY::w('as_html') ? Show::html(Show::doc($src)) : tag(Show::doc($src), 'id="md-doc"') . css(MD::$MD->css);
        }
    }

    function css($src, $m) {
        return Show::css($src);
    }

    function xml($src, $m) {
        if ('hightlight' == $m)
            return Show::html($src);
        $xml = new XML($src, 'beautifier' == $m ? 2 : 0);
        $dump = 'dump' == $m;
        if ($dump || 'tok' == $m)
            return $this->t_token->token_xml($xml, $dump);
        return Show::html($xml);
    }

    function yml($src, $m) {
        return Show::yaml($src);
    }

    function diff($fn, $m) {
        if ('git' == $m)
            return $this->t_git->show($fn);
        $old = $this->t_git->file($fn);
        $new = file_get_contents(SKY::w('repo') . "/$fn");
        Show::scheme('z_php');
        $x = Show::xdata($diff = Show::diff($new, $old));
        $x->len = strlen($diff);
        $x->colors = $m[0];
        $new = Show::table(explode("\n", html($new)), clone $x, false);
        $x->invert = true;
        $old = Show::table(explode("\n", html($old)), $x, false);
        return view('parse.diff', [
            'old' => $old,
            'new' => $new,
        ]);
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
