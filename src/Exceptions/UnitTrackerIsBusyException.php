<?php
namespace Pluf\Scion\Exceptions;

/**
 * If the unit tracker is busy to do a process and you try to call
 * the process agin, this one rise.
 *
 * @author maso
 * @since 7
 */
class UnitTrackerIsBusyException extends \RuntimeException
{
}

