<?php
namespace Skfw\Virtualize;

use Exception;
use PathSys;
use Skfw\Interfaces\IVirtStdPathResolver;

class VirtStdPath
{
    private string $_basedir;  // mocking root directory
    private string $_workdir;

    /**
     * @throws Exception
     */
    public function __construct(string $basedir, ?string $workdir = null)
    {
        // basedir is local directory
        // workdir must be inside basedir

        $resolver = new VirtStdPathResolver($basedir);
        if (!$resolver->is_base_path()) throw new Exception('base directory is not valid');
        $this->_basedir = $resolver->pack($resolver->paths());

        // workdir must be checking first
        // basedir: /home/user
        // workdir: /home/user/foo | user/foo | foo

        // base: ['home', 'user']
        // work:         ['user', 'foo']
        // main:                 ['foo']

        $this->_workdir = $workdir ?? $basedir;
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
