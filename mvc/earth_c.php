<?php

class earth_c extends Controller
{
    function head_y($action) { // class earth_c extends Controller class earth_c extends Controller
        Plan::_r('conf.php');
        $vars = Plan::cfg_gq('earth_vars.txt');
        SKY::ghost('w', $vars, function ($s) {
            Plan::cfg_p(['earth', 'earth_vars.txt'], $s);
        });
    }

    function tail_y() {
        if (!MVC::$layout)
            return;
        '_earth?ware' == URI or SKY::w('last_link', URI);
        return parent::tail_y();
    }

    function error_y($action) {
        return ['var' => 222];
    }

    function a_ware() {
        '_earth?ware' != $this->w_last_link or $this->w_last_link = '';
        jump($this->w_last_link ?: '_earth?sandbox=php');
    }

    function a_sandbox() {
        SKY::w('last_sand', $this->_2);
        $list = array_map(function ($v) {
            return a(substr(basename($v, '.txt'), 4), ['sky.d.preset($(this))']);
        }, Plan::cfg_b("sand/$this->_2-*"));
        return [
            'list' => implode('', $list),
            'sands' => [
                'php' => 'PHP',
                'jsc' => 'Javascript',
                'sql' => 'SQL',
            ],
        ];
    }

    function j_preset() {
        return [
            'fn' => $fn = 'sand/' . $_POST['n'] . '.txt',
            'ary' => explode('~', Plan::cfg_g($fn)),
        ];
    }

    function j_php_run() {
        $this->debug = 1;
        echo html($_POST['s']) . '<hr>';
        ob_start();
        eval('' . $_POST['s'] . ';');
        SKY::w('sand_esc', $_POST['chk']);
        echo $_POST['chk'] ? html(ob_get_clean()) : ob_get_clean();
    }

    function j_pre_save() {
        echo $this->t_earth->presave($_POST['f'], $_POST['s']);
    }


    function j_save() {
        $ary = $this->t_earth->save();
        $ary[1] ? json(['jump' => "?$_POST[tbl]=" . $ary[1]]) : print($ary[0]);
    }

    function j_edit() {
        SKY::w('width', 100 == $this->w_width ? 55 : 100);
        echo $this->w_last_link;
        if ($_POST['add'])
            echo '&add';
    }

    function j_qq() {
       echo 11;
        return 777;
    }

    function a_port() {
        return [];
    }

    function a_phar() {
        return [];
    }

    function a_merc() {
        return $this->a_func('merc');
    }

    function a_docs() {
        return $this->a_func('docs');
    }

    function a_func($x = 'func') {
        SKY::w('last_' . $x, $this->_2);
        return [
            'e_earth' => $this->t_earth->listing(),
            'width' => $this->w_width ?: 100,
        ];
    }
}
