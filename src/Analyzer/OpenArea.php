<?php

declare(strict_types=1);

namespace BattleSnake\Analyzer;

use BattleSnake\Domain\Board;
use BattleSnake\Domain\Coordinate;
use BattleSnake\Domain\Direction;

/**
 * Counts the squares reachable from a position: a flood fill over the four
 * neighbours, inside the board, through squares no snake body occupies and
 * that are not in $alsoBlocked. The start square itself is not counted.
 *
 * Every body square blocks, tails included: the squeeze plays out over many
 * turns, so this does not predict tails moving.
 */
class OpenArea
{
    public function __invoke(Board $board, Coordinate $from, Coordinate ...$alsoBlocked): int
    {
        $seen = [self::key($from) => true];
        foreach ($alsoBlocked as $blocked) {
            $seen[self::key($blocked)] = true;
        }

        $count = 0;
        $queue = [$from];
        while ($queue !== []) {
            $current = \array_shift($queue);
            foreach (Direction::cases() as $direction) {
                $next = $current->adjacent($direction);
                $key = self::key($next);
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                if (! $board->contains($next) || $board->hasSnakeAt($next)) {
                    continue;
                }
                $count++;
                $queue[] = $next;
            }
        }

        return $count;
    }

    private static function key(Coordinate $position): string
    {
        return $position->x . ',' . $position->y;
    }
}
