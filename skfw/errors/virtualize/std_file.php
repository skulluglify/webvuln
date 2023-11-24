<?php
namespace Skfw\Errors\Virtualize;

use Exception;
use Throwable;


class VirtStdFileError extends Exception
{
}

class VirtStdFileTypeDoNotMatch extends VirtStdFileError
{
}

class VirtStdFileSizeDoNotMatch extends VirtStdFileError
{
}
