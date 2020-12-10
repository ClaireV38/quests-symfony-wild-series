<?php

namespace App\Service;

class Slugify
{
    /**
     * @param string $input
     * @return string
     */
    public function generate(string $input) : string
    {
        $output = str_replace(" ", "-", "$input");
        return $output;
    }
}