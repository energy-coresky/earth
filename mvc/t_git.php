<?php

class t_git extends Model_t
{
    function show($fn) {
        if (is_int($m = $this->commits()))
            return pre("Return code: $m");
        $out = th(['#', 'Date', 'Changed', 'Name', 'Commit'], 'width="79%"');
        foreach ($m as $i => $v) {
            ++$i;
            preg_match_all("/ M\t([^\r\n]+)/s", $v[4], $mm, PREG_SET_ORDER);
            $mm = array_map(fn($v) => a($v[1], ["sky.d.diff('$i:$v[1]')"]), $mm);
            $out .= td([
                $i, $v[2],
                implode('<br>', $mm),
                $v[3], $v[1],
            ]);
        }
        return view('parse.git', [
            'table' => "$out</table>",
            'repos' => $this->repos(),
        ]);
    }

    function sys($com, &$code) {
        ob_start();
        system($com, $code);
        return ob_get_clean();
    }

    function file(&$fn) {
        if (!strpos($fn, ':'))
            return file_get_contents($fn . '1');
        [$n, $fn] = explode(':', $fn, 2);
        $commit = $this->commits($n);
        $dir = SKY::w('repo');
        return $this->sys("cd $dir && git show $commit:$fn", $code);
    }

    function repos($n = null) {
        if (!$dirs = Plan::cache_rq($addr = ['main', 'mem_earth_repo.php'])) {
            $dirs = ['.' => 0, DIR_S => 0];
            foreach (SKY::$plans as $ware => $cfg)
                'main' == $ware or $dirs[$cfg['app']['path']] = 0;
            foreach ($dirs as $dir => &$url)
                if (is_dir("$dir/.git"))
                    $url = trim($this->sys("cd $dir && git remote get-url origin", $code));
            Plan::cache_s($addr, Boot::auto($dirs));
        }
        return option(SKY::w('repo'), $dirs);
    }

    function commits($n = null) {
        $dir = SKY::w('repo');
        $sys = $this->sys("cd $dir && git whatchanged -30", $code);
        if ($code)
            return $code;
        $re = "commit (\w{40})\s+.*?Date:\s+([\w :\+\-]+)[\r\n]+ {4}([^\r\n]+)(.+?)\n\n";
        preg_match_all("/$re/s", $sys, $m, PREG_SET_ORDER);
        return $n === null ? $m : $m[$n][1];
    }
}
