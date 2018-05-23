<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 17/4/4
 * Time: 上午2:19
 */

namespace Encore\Admin\Workflow\process;


use Encore\Admin\Workflow\Inject\InjectorInterface;

Interface ProcessInterface
{

    /**
     * @return InjectorInterface
     */
    public function getContext();

    public function run();
}