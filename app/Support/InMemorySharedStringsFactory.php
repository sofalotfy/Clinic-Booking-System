<?php

namespace App\Support;

use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactoryInterface;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyInterface;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\FileBasedStrategy;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\InMemoryStrategy;

/**
 * OpenSpout budgets 12KB per shared string, so a drug sheet with ~90k unique
 * strings is estimated at over 1GB and falls back to its file based cache,
 * which reloads a temp file on almost every cell and takes minutes to read.
 * The strings really take a few hundred bytes each, so keep them in memory.
 */
class InMemorySharedStringsFactory implements CachingStrategyFactoryInterface
{
    private const MAX_IN_MEMORY_STRINGS = 1_000_000;

    private const MAX_NUM_STRINGS_PER_TEMP_FILE = 10_000;

    public function createBestCachingStrategy(?int $sharedStringsUniqueCount, string $tempFolder): CachingStrategyInterface
    {
        if ($sharedStringsUniqueCount !== null && $sharedStringsUniqueCount <= self::MAX_IN_MEMORY_STRINGS) {
            return new InMemoryStrategy($sharedStringsUniqueCount);
        }

        return new FileBasedStrategy($tempFolder, self::MAX_NUM_STRINGS_PER_TEMP_FILE);
    }
}
