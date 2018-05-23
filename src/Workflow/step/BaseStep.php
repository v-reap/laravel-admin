<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 17/4/4
 * Time: 上午2:18
 */

namespace Encore\Admin\Workflow\step;


use Encore\Admin\Workflow\Inject\Chains;
use Encore\Admin\Workflow\process\ProcessInterface;

class BaseStep implements StepInterface
{

    private $process;
    private $closures;
    private $result;
    private $status;
    private $id;

    public function __construct(ProcessInterface $process,$id = null)
    {
        $this->process = $process;
        $this->closures = array(
            self::BEFORE => array(),
            self::RUNNING => array(),
            self::AFTER => array()
        );
        $this->status = self::RESULT_NOT_RUN;
        $this->id = $id ? : md5(uniqid(mt_rand(), true));
    }

    public function setBeforeRun(Array $closures){
        $this->closures[self::BEFORE] = $closures;
    }

    public function setRunning(Array $closures){
        $this->closures[self::RUNNING] = $closures;
    }

    public function setAfterRun(Array $closures){
        $this->closures[self::AFTER] = $closures;
    }

    public function checkBeforeRun()
    {
        $chains = new Chains($this->process->getContext());
        return $chains->chain($this->closures[self::BEFORE])->runWild();
    }

    public function checkAfterRun()
    {
        $chains = new Chains($this->process->getContext());
        return $chains->chain($this->closures[self::AFTER])->runWild();
    }

    public function running()
    {
        $chains = new Chains($this->process->getContext());
        return $chains->chain($this->closures[self::RUNNING])->runWild();
    }

    public function run(){
        $before = $this->checkBeforeRun();
        if (!is_null($before)){
            $this->status = self::RESULT_PANIC_ON_BEFORE;
            $this->result = $before;
            return $this->getResultStatus();
        }
        $running = $this->running();
        if (!is_null($running)){
            $this->status = self::RESULT_PANIC_ON_RUNNING;
            $this->result = $running;
            return $this->getResultStatus();
        }
        $after = $this->checkAfterRun();
        if (!is_null($after)){
            $this->status = self::RESULT_PANIC_ON_AFTER;
            $this->result = $after;
        }else{
            $this->status = self::RESULT_SUCCESS;
        }
        return $this->getResultStatus();
    }

    public function getResultStatus()
    {
        return $this->status;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getId()
    {
        return $this->id;
    }
}