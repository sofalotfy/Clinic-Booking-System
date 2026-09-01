<?php

namespace App\Services\DaysInstances\Modifications;

use Carbon\Carbon;

/**
 * Wraps a set of time slots for a day and tracks which ones
 * have already been claimed, so callers don't have to juggle
 * two parallel arrays (slots + used flags) themselves.
 *
 * Assumes findClosestAvailable() is called with non-decreasing
 * $target values (i.e. appointments processed in chronological
 * order). A forward-moving cursor tracks the last search
 * position so each call doesn't re-scan from scratch.
 */
class SlotPool
{
    /** @var Carbon[] */
    private array $slots;

    /** @var bool[] */
    private array $used;

    /** Tracks the last insertion point found, since targets arrive sorted. */
    private int $cursor = 0;

    public function __construct(array $slots)
    {
        $this->slots = array_values($slots);
        $this->used = array_fill(0, count($this->slots), false);
    }

    public function markUsed(int $index): void
    {
        $this->used[$index] = true;
    }

    public function slotAt(int $index): Carbon
    {
        return $this->slots[$index];
    }

    /**
     * Find the index of the closest slot to $target that isn't
     * already used.
     *
     * IMPORTANT: assumes successive calls pass non-decreasing
     * $target values. The cursor only ever moves forward, so if
     * you need to search out of order, use findClosestAvailableUnordered()
     * instead (falls back to a fresh binary search).
     */
    public function findClosestAvailable(Carbon $target): ?int
    {
        $count = count($this->slots);

        if ($count === 0) {
            return null;
        }

        // advance the cursor forward only, up to the first slot
        // that is not before the target
        while ($this->cursor < $count - 1 && $this->slots[$this->cursor]->lt($target)) {
            $this->cursor++;
        }

        return $this->expandFromInsertionPoint($this->cursor, $target);
    }

    /**
     * Fallback for out-of-order lookups (e.g. if you ever need to
     * search for a target earlier than one already searched).
     * Does a fresh binary search rather than trusting the cursor.
     */
    public function findClosestAvailableUnordered(Carbon $target): ?int
    {
        $count = count($this->slots);

        if ($count === 0) {
            return null;
        }

        $insertionPoint = $this->binarySearchInsertionPoint($target);

        return $this->expandFromInsertionPoint($insertionPoint, $target);
    }

    private function binarySearchInsertionPoint(Carbon $target): int
    {
        $low = 0;
        $high = count($this->slots) - 1;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);

            if ($this->slots[$mid]->lt($target)) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return $low;
    }

    private function expandFromInsertionPoint(int $insertionPoint, Carbon $target): ?int
    {
        $count = count($this->slots);
        $left = $insertionPoint - 1;
        $right = $insertionPoint;

        while ($left >= 0 || $right < $count) {
            while ($left >= 0 && $this->used[$left]) {
                $left--;
            }

            while ($right < $count && $this->used[$right]) {
                $right++;
            }

            if ($left < 0) {
                return $right < $count ? $right : null;
            }

            if ($right >= $count) {
                return $left;
            }

            $leftDiff = abs($target->diffInSeconds($this->slots[$left], false));
            $rightDiff = abs($target->diffInSeconds($this->slots[$right], false));

            return $leftDiff <= $rightDiff ? $left : $right;
        }

        return null;
    }
}