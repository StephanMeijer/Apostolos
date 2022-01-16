<?php

declare(strict_types=1);

namespace App\DataStructure;

enum AdapterAction {
    case CREATE;
    case UPDATE;
    case DELETE;
    case LIST;
};
