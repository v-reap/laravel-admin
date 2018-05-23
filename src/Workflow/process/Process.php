<?php
namespace Encore\Admin\Workflow\process;

use Encore\Admin\Workflow\Inject\Injector;
use Encore\Admin\Workflow\route\Route;
use Encore\Admin\Workflow\step\StepInterface;

Class Process implements ProcessInterface
{

    private $context;
    private $route;
    private $steps;
    private $step_id_now;

    public function __construct(Injector $inj)
    {
        $this->context = $inj;
        $this->route = new Route();
    }

    public function getContext()
    {
        return $this->context;
    }

    public function run(){
        $step_id = $this->route->getFirst();
        while($step_id){
            $this->step_id_now = $step_id;
            /* @var $step \Encore\Admin\Workflow\step\StepInterface */
            $step = $this->steps[$step_id];
            $status = $step->run();
            if ($status == StepInterface::RESULT_SUCCESS){
                $next = $this->route->getNextStep($step_id);
                if ($next instanceof \Closure){
                    $next = $this->context->call($next);
                }
                if ($next){
                    $step_id = $next;
                }else{
                    break;
                }
            }else{
                break;
            }
        }
        return $this->isEnd();
    }

    public function isEnd(){
        return empty($this->route->getNextSteps($this->step_id_now));
    }

    public function first(StepInterface $step,$next_ids = array(),$next_closure = null){
        $step_id = $step->getId();
        $this->route->setFirst($step_id);
        return $this->step($step,$next_ids,$next_closure);
    }

    public function step(StepInterface $step,$next_ids = array(),$next_closure = null)
    {
        $step_id = $step->getId();
        $this->steps[$step_id] = $step;
        $this->route->setNextSteps($step_id,$next_ids);
        if ($next_closure) $this->route->setNextLogic($step_id,$next_closure);
        return $this;
    }

    /**
     * @return StepInterface
     */
    public function getStepNow(){
        return $this->steps[$this->step_id_now];
    }
}