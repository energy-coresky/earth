<?php

class t_earth extends Model_t
{
    function __construct() {
        $this->table = $_POST['tbl'] ?? $this->_1;
        parent::__construct();
    }

    function head_y() {
        return $this->edd;
    }

    static function code($s) {
        $re = '<pre><code class="language\-(jet|php)">(.*?)</code></pre>';
        return preg_replace_callback("@$re@s", function ($m) {
            if ('php' == $m[1])
                return Display::php(unhtml($m[2]), '', true);
            return Display::jet(unhtml($m[2]), '-', true);
        }, $s);
    }

    function presave($f, $s) {
        if (!$f)
            return '-';
        list ($n, $f) = explode('.', $f, 2);
        $ary = explode("\n~\n", unl(Plan::glob_g($f)));
        $ary[$n] = $s;
        Plan::glob_p($f, implode("\n~\n", $ary));
        return "OK";
    }

    function ins() {
        $this->insert([
            'title' => '',
            'md' => '',
            '!dt_c' => '$now',
            '!dt_u' => '$now',
        ]);
    }

    function save() {
        $this->update([
            'md' => $_POST['s'],
            '!dt_u' => '$now',
        ], $_POST['id']);
        return md($_POST['s']);
    }

    function topic($md) {
        MVC::in_tpl(false);
        preg_match_all("!^### (.*)!m", $md, $m);
        $s = view('earth.topic', ['ary' => $m]);
        MVC::in_tpl();
        return $s;
    }

    function listing() {
        return [
            'query' => $this->all(),
            'id' => $this->_2 ?: 1,
            'func' => function ($e, $row) {
                if ($act = $row->id == $e->id)
                    $e->doc = [$row->md, md($row->md), $row->title, $this->topic($row->md), $row->dt_u];
                return $act;
            },
        ];
    }
}
