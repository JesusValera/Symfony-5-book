<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Cache\CacheInterface;

final class StepInfoCommand extends Command
{
    protected static $defaultName = 'app:step:info';

    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->cache->get('app.display_msg', static function(CacheItem $item) {
            $process = new Process(['echo', 'hello']);
            $process->mustRun();
            $item->expiresAfter(30);

            return $process->getOutput();
        });

        $output->write($result);

        return 0;
    }
}
