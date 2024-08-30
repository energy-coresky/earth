<?php

class t_zml extends Model_t
{
    function view($fn) {
        SKY::w('zml_file', $fn);
        if (!is_file($fn))
            return $this->dev->error("File `$fn` not found");
        $zml = new ZML($fn);
        echo Debug::out($zml->bang);
        $ary = [];
        foreach ($zml->read() as $pos => $y)
            $ary[$pos] = trim($y->line);
        echo Debug::out($ary);
    }
}
