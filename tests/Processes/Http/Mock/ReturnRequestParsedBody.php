<?php
namespace Pluf\Tests\Processes\Http\Mock;

use Psr\Http\Message\ServerRequestInterface;

class ReturnRequestParsedBody
{

    public function __invoke(ServerRequestInterface $request)
    {
        return $request->getParsedBody();
    }
}

