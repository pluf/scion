<?php
namespace Pluf\Scion\Process;

use Psr\Http\Message\ServerRequestInterface;
use Pluf\Scion\UnitTrackerInterface;

class HttpProcess
{

    public string $regex;

    public array $methods;

    public function __construct(string $regex, array $methods = [
        'GET',
        'POST',
        'DELETE'
    ])
    {
        $this->regex = $regex;
        $this->methods = $methods;
    }

    public function __invoke(ServerRequestInterface $request, UnitTrackerInterface $unitTracker)
    {
        $uri = $request->getUri();
        $requestPath = $uri->getPath();
        $method = $request->getMethod();
        $match = [];
        if (! in_array($method, $this->methods) || ! preg_match($this->regex, $requestPath, $match)) {
            return $unitTracker->jump();
        }
        return $unitTracker->next(array_merge($match, [
            'request' => $request->withUri($uri->withPath(substr($requestPath, strlen($match[0]))))
        ]));
    }
}

