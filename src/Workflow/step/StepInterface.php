<?php

/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 17/4/4
 * Time: 上午2:15
 */

namespace Encore\Admin\Workflow\step;

Interface StepInterface
{

    const BEFORE = 1;
    const RUNNING = 2;
    const AFTER = 3;

    const RESULT_PANIC_ON_BEFORE = -3;
    const RESULT_PANIC_ON_RUNNING = -2;
    const RESULT_PANIC_ON_AFTER = -1;
    const RESULT_NOT_RUN = 0;
    const RESULT_SUCCESS = 1;

    public function checkBeforeRun();

    public function checkAfterRun();

    public function running();

    public function run();

    public function getResult();

    public function getResultStatus();

    public function getId();
}