<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 17/4/4
 * Time: 上午2:39
 */

namespace Encore\Admin\Workflow\step;


use Encore\Admin\Workflow\process\ProcessInterface;

class AutoStep extends BaseStep implements StepInterface
{

    public function __construct(ProcessInterface $process, $id = null,$before = array(),$running = array(),$after = array())
    {
        parent::__construct($process, $id);
        $this->setBeforeRun($before);
        $this->setRunning($running);
        $this->setAfterRun($after);
        return $this->getId();
    }

}