<?php

/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 17/4/4
 * Time: 上午2:40
 */

namespace Encore\Admin\Workflow\route;

class Route
{

    private $prev_routes;
    private $next_routes;
    private $next_logic;
    private $first;

    /**
     * @return string
     */
    public function getFirst()
    {
        return $this->first;
    }

    public function setFirst($first)
    {
        $this->first = $first;
    }

    public function setPrevSteps($source,$steps){
        $this->prev_routes[$source] = $steps;
    }

    public function setNextSteps($source,$steps){
        $this->next_routes[$source] = $steps;
    }

    public function getNextSteps($source){
        return $this->next_routes[$source];
    }

    public function getPrevSteps($source){
        return $this->prev_routes[$source];
    }

    public function setNextLogic($source,$closure){
        $this->next_logic[$source] = $closure;
    }

    public function getNextStep($source){
        if (isset($this->next_logic[$source])) return $this->next_logic[$source];
        if (isset($this->next_routes[$source][0])) return $this->next_routes[$source][0];
        return null;
    }
}