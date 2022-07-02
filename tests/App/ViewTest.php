<?php

namespace NandesSimanjuntak\Belajar\PHP\App;

use PHPUnit\Framework\TestCase;
use NandesSimanjuntak\Belajar\PHP\MVC\App\View;

class ViewTest extends TestCase
{

    public function testRender()
    {
        View::render('Home/index', [
            "PHP Login Management"
        ]);

        $this->expectOutputRegex('[PHP Login Management]');
        $this->expectOutputRegex('[html]');
        $this->expectOutputRegex('[body]');
        $this->expectOutputRegex('[Login Management]');
        $this->expectOutputRegex('[Login]');
        $this->expectOutputRegex('[Register]');
    }

}