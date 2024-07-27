<?php

class m_air extends Model_m
{
    function parse(&$article) {
        if (!preg_match("/^(.*?BEGIN \-\->)(.*?)(<!\-\- END.*)$/s", $article, $match))
            return;

        [, $begin, $tbl, $end] = $match;
        $x = [0, 0, 0, 2, 1, 0, 0];
        $other = ['sky.php', 'sky', 'etc/moon.php', 'assets/dev.css', 'assets/dev.js', 'assets/sky.css', 'assets/sky.js'];

        $tbl = explode("\n", unl(trim($tbl)));
        $head = array_shift($tbl);
        $head .= "\n" . array_shift($tbl);
        $tbl = array_map(fn($r) => explode(' | ', $r), $tbl);
        $tbl = array_filter($tbl, fn($r) => count($r) >= 3);
        $tbl = array_combine(array_map(fn($r) => $r[0], $tbl), array_map(fn($r) => $r[3] ?? $r[2], $tbl));

        $w2 = Rare::list_path(DIR_S . "/w2");
        foreach ($w2 as &$fn) {
            $txt = file_get_contents($fn);
            $x[0] += ($lines = 1 + substr_count($txt, "\n"));
            $x[1] += ($sz = filesize($fn));
            $sz = $sz > 1024 ? round($sz / 1024, 1) . ' kB' : "$sz Bytes";
            if ('jet' == substr($fn, -3)) {
                $cnt = 'template';
            } else {
                $ary = Globals::file($fn, $txt);
                $x[2] += ($n2 = count($ary['FUNCTION']));
                $x[3] += ($n3 = count($ary['CLASS']));
                $x[4] += ($n4 = count($ary['INTERFACE']));
                $x[5] += ($n5 = count($ary['TRAIT']));
                $x[6] += ($n6 = count($ary['ENUM']));
                $cnt = $n2 . ', ' . ($n3 + $n4 + $n5 + $n6);
            }
            $fn = implode(' | ', [$base = basename($fn), "$lines<br>$sz", $cnt, $tbl[$base] ?? '---']);
        }

        foreach ($other as &$fn) {
            $txt = file_get_contents(DIR_S . "/$fn");
            $x[0] += ($lines = 1 + substr_count($txt, "\n"));
            $x[1] += ($sz = filesize(DIR_S . "/$fn"));
            $sz = $sz > 1024 ? round($sz / 1024, 1) . ' kB' : "$sz Bytes";
            $fn = implode(' | ', [$fn, "$lines<br>$sz", $tbl[$fn] ?? '---']);
        }

        $tbl = implode("\n", $w2) . "\n\nВсего в папке **w2**: " . count($w2) . " файлов. "
            . "Другой основной код **Coresky**, смотрите в следующей таблице:\n\n"
            . "Файл | Строк,<br>размер | Описание\n:--- | :--- | :---\n" . implode("\n", $other)
            . "\n\nИтого **Coresky** это: " . (count($w2) + count($other)) . " файлов, $x[0] строк кода, "
            . round($x[1] / 1024, 1) . " kBytes. В глобальной области видимости: $x[2] именованных функций PHP, "
            . ($x[3] + $x[4] + $x[5] + $x[6]) . " классов PHP, из них $x[4] интерфейса, $x[5] трейта и $x[6] перечислений.";
        $article = "$begin\n$head\n$tbl\n$end";
    }
}
