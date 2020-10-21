<?php
namespace Pluf\Scion\Exceptions;

use RuntimeException;

/**
 * When it is impossible to load units (for e.g.
 * file not exist), the new instance of
 * this class be thrown.
 *
 * @author maso
 *        
 */
class ImposibleToLoadUnits extends RuntimeException
{
}

