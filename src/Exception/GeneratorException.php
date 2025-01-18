<?php

declare(strict_types=1);

namespace Bone\Generator\Exception;

use Exception;

class GeneratorException extends Exception
{
    const FILE_EXISTS = 'The file already exists';
    const FILE_WRITE_ERROR = 'Could not write file %s';
}
