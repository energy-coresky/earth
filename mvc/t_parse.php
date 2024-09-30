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
        $php = new PHP(file_get_contents($fn));
        $php->parse();
        echo pre(html(var_export($php->array, true)));
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
