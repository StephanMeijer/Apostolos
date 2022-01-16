<?php

declare(strict_types=1);

namespace App\Exception;

use App\DataStructure\AdapterAction;
use Exception;
use Throwable;

class AdapterDoesNotSupportActionException extends Exception {
    public function __construct(AdapterAction $action, Throwable $previous = null)
    {
        $message = sprintf("Adapter does not support action %s", $action->name);
        parent::__construct($message, 0, $previous);
    }
}