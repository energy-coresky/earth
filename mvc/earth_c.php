<?php

class earth_c extends Controller
{
    function head_y($action) {
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

    function j_sql_run() {
        SKY::$debug = 1;
        $s = $_POST['s'];
        [$ware, $db] = explode('::', $_POST['db']);
        $dd = SQL::open($db, $ware);
        $sql = '';
        if (-1 == ($pos = $_POST['pos'])) {
            $ary = Rare::split($s);
            printf("Executed %d SQL queries:<hr>%s<hr>last query result:<br>", count($ary), implode('<br>', $ary));
            if ($ary)
                for ($sql = array_shift($ary); count($ary); $sql = array_shift($ary))
                    $dd->sqlf($sql);
        } else {
            for ($i = 0; false !== strpos($s, $rand = strand()); )
                if (++$i > 99)
                    throw new Error(1);
            $s = mb_substr($s, 0, $pos) . $rand . mb_substr($s, $pos);
            foreach (Rare::split($s) as $one) {
                $ary = explode($rand, $one);
                if (2 == count($ary)) {
                    $sql = trim($ary[0] . $ary[1]);
                    break;
                }
            }
        }
        if ('' === $sql) {
            L::r('empty SQL query');
            return;
        }

        echo html($sql) . '<hr>';
        $ary = [];
        $values = function ($v) {
            $v = array_values($v);
            foreach ($v as &$val)
                !$_POST['chk'] or $val = html($val);
            return $v;
        };
        if (is_object($q = $dd->sqlf($sql))) {
            if ($q->has_result()) {
                for (; $row = $dd->one($q); $ary[] = $row);
                if ($ary) {
                    $i = 0;
                    echo th([-2 => '##'] + array_keys(current($ary)), 'id="table"');
                    foreach ($ary as $k => $v)
                        echo td([-1 => [1 + $i, 'style="width:5%"']] + $values($v), eval(zebra));
                    echo '</table>';
                } else {
                    echo L::r('empty result') . '<hr>';
                }
                return;
            }
        }
        $res = print_r($q, true);
        echo $_POST['chk'] ? html($res) : $res;
    }

    function j_php_run() {
        SKY::$debug = 1;
        echo html($_POST['s']) . '<hr>';
        ob_start();
        SKY::w('sand_esc', $chk = $_POST['chk']);
        try {
            eval($_POST['s'] . ';');
            $res = ob_get_clean();
        } catch (Throwable $e) {
            $chk = false;
            $res = '<hr>' . html($e->getMessage()) . '<hr>' . html($e->getTraceAsString());
        }
        echo $chk ? html($res) : $res;
    }

    function j_pre_save() {
        echo $this->t_earth->presave($_POST['f'], trim($_POST['s']));
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

    function a_parse() {
        return $this->t_parse->a($this->_2);
    }

    function j_repo() {
        SKY::w('repo', $_POST['d']);
        return true;
    }

    function j_parse() {
        return $this->t_parse->j($_POST['fn']);
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
        return $this->t_earth->listing();
    }

    function default_j() {
        $id = $this->_3;
        unset($_GET['id']);
        $_GET += [$id => $id];
        SKY::w('last_link', "_earth?$this->_1=$id");
        json([
            'html' => view('earth.default', $this->a_func($this->_1)),
            'h3' => $this->dt_u,
        ]);
    }
}
