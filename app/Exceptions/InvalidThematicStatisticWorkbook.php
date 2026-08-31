<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;

class InvalidThematicStatisticWorkbook extends Exception implements ShouldntReport {}
