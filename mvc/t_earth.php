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
        $conf = Plan::_r('conf.php');
        if ($this->is_merc)
            $conf = require Plan::_obj(0)->path . '/../mercury/conf.php';
        SKY::$databases += $conf['app']['databases'];
        return SQL::open($this->is_merc ? '_w' : '_e');
    }

    function md($text) {
        if ($this->is_merc)
            return tag(html($text), '', 'pre');
        return Display::md($text);
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
        if ($id && 'docs' == $this->table) {
            $fn = __DIR__ . '/../../../air.wiki/';
            $fn .= str_replace(' ', '-', $this->cell(['id=' => $id], 'title')) . '.md';
            if (is_file($fn))
                file_put_contents($fn, unl($ary['md']));
        }
        return [$this->md($_POST['s']), $id ? 0 : $new_id];
    }

    function topic($md) {
        preg_match_all("!^### (.*)!m", $md, $m);
        $s = view('earth.topic', ['ary' => $m]);
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
