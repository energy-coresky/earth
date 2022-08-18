<?php

class t_earth extends Model_t
{
    private $is_merc;

    function __construct() {
        $x = $_POST['tbl'] ?? $this->_1;
        if ($this->is_merc = 'merc' == $x)
            $x = 'tpl';
        $this->table = $x;
        parent::__construct();
    }

    function head_y() {
        if ($this->is_merc)
            require DIR_EARTH . '/../mercury/conf.php';
        return SQL::open($this->is_merc ? '_w' : '_e');
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
        $ary = explode("\n~\n", unl(Plan::cfg_g($f)));
        $ary[$n] = $s;
        Plan::cfg_p($f, implode("\n~\n", $ary));
        return "OK";
    }

    function save() {
        $id = $_POST['id'];
        $ary = [
            $this->is_merc ? 'tpl' : 'md' => $_POST['s'],
            '!dt_u' => '$now',
        ];
        if ($_POST['title'])
            $ary += [$this->is_merc ? 'fn' : 'title' => $_POST['title']];
        $id ? $this->update($ary, $id) : ($new_id = $this->insert($ary + ['!dt_c' => '$now']));
        return [$this->md($_POST['s']), $id ? 0 : $new_id];
    }

    function md($text) {
        if ($this->is_merc)
            return tag(html($text), '', 'pre');
        $md = new Parsedown();
        return t_earth::code($md->text($text));
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
            'id' => isset($_GET['add']) ? 0 : ($this->_2 ?: 1),
            'func' => function ($e, $row) {
                if ($this->is_merc) {
                    $row->md = $row->tpl;
                    $row->title = $row->fn;
                }
                if ($act = $row->id == $e->id)
                    $e->doc = [$row->md, $this->md($row->md), $row->title, $this->topic($row->md), $row->dt_u];
                return $act;
            },
        ];
    }
}
