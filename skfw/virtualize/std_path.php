<?php
namespace Skfw\Virtualize;

use Exception;
use PathSys;
use Skfw\Interfaces\Virtualize\IVirtStdPath;
use Stringable;

// TODO: idk how to continue about this object class idea!

class VirtStdPath implements Stringable, IVirtStdPath
{
    private VirtStdPathResolver $_basedir;  // mocking root directory
    private VirtStdPathResolver $_workdir;

    /**
     * @throws Exception
     */
    public function __construct(?string $basedir = null, ?string $workdir = null)
    {
        // basedir is local directory
        // workdir must be inside basedir

        // set default!
        $basedir = $basedir ?? __DIR__;
        $workdir = $workdir ?? __DIR__;

        // resolving path on basedir!
        $resolver = new VirtStdPathResolver($basedir);
        if (!$resolver->is_base_path())
        {
            $pack = VirtStdPathResolver::pack($resolver->paths(), base: true, sys: PathSys::POSIX);
            $resolver = new VirtStdPathResolver(__DIR__ . $pack);
        }
        $this->_basedir = $resolver;

        // resolving path on workdir!
        $resolver = new VirtStdPathResolver($workdir);
        if (!$resolver->is_base_path())
        {
            $pack = VirtStdPathResolver::pack($resolver->paths(), base: true, sys: PathSys::POSIX);
            $resolver = new VirtStdPathResolver(__DIR__ . $pack);
        }
        $this->_workdir = $resolver;
    }
    public function __toString(): string
    {
        return str($this->_workdir);
    }

    public function basedir(): string
    {
        return $this->_basedir;
    }
    public function workdir(): string
    {
        return $this->_workdir;
    }
}
