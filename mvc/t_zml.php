<?php

class t_zml extends Model_t
{
    function view($fn) {
        SKY::w('zml_file', $fn);
        if (!is_file($fn))
            return $this->dev->error("File `$fn` not found");
        echo $fn;

    }
}
