<?php

namespace Encore\Admin\Workflow\Inject;


interface InjectorInterface
{

    public function map($k,$v,$c);

    public function get($k);

    public function produce($k,$params = array());

    public function call(\Closure $c,$p = array());

    public function callInClass($c,$a,$p = array());
}