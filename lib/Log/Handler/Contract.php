<?php
namespace SLiMS\Log\Handler;

abstract class Contract
{
    /**
     * Write new data
     *
     * @param string $type
     * @param string $value_id
     * @param string $location
     * @param string $message
     * @param string $submod
     * @param string $action
     * @return void
     */
    protected abstract function write(string $type, string $value_id, string $location, string $message, string $submod='', string $action='');

    /**
     * Read log data
     *
     * @return void
     */
    protected abstract function read(?Object $formatter = null): Contract;

    /**
     * Flush log data
     *
     * @return void
     */
    protected abstract function truncate();

    /**
     * Download log as plain text etc
     *
     * @return void
     */
    protected abstract function download();

    public abstract function __toString():string;
}