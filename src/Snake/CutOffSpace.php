<?php

declare(strict_types=1);

namespace BattleSnake\Snake;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Analyzer\OpenArea;
use BattleSnake\Domain\Battlesnake;
use BattleSnake\Domain\Coordinate;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Strategy;

/**
 * Squeezes the nearest shorter snake: among the SAFE moves, the one that
 * leaves that snake's head the fewest reachable squares, with our body in its
 * new place. A move that leaves our own head fewer squares than our length is
 * never taken. Ties go to the first direction found.
 *
 * With no shorter snake, no SAFE move, or no move that keeps us room, it
 * abstains: it votes a square the MoveClassifier labels END.
 */
final class CutOffSpace implements Strategy
{
    public function __construct(
        private readonly MoveClassifier $classifier,
        private readonly OpenArea $openArea,
    ) {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $you = $state->you;
        $head = $you->head;
        $target = $head === null ? null : self::nearestShorter($state, $head);
        $best = null;
        $bestArea = \PHP_INT_MAX;
        $dead = null;

        foreach (($this->classifier)($state) as $move) {
            if ($move->is(Classification::END)) {
                $dead ??= $move->direction;
                continue;
            }
            if ($head === null || $target?->head === null || ! $move->is(Classification::SAFE)) {
                continue;
            }

            $square = $head->adjacent($move->direction);
            if (($this->openArea)($state->board, $square) < $you->length) {
                continue;
            }

            $theirs = ($this->openArea)($state->board, $target->head, $square);
            if ($theirs < $bestArea) {
                $bestArea = $theirs;
                $best = $move->direction;
            }
        }

        return $best ?? $dead ?? Direction::UP;
    }

    private static function nearestShorter(GameState $state, Coordinate $head): ?Battlesnake
    {
        $nearest = null;
        $nearestDistance = \PHP_INT_MAX;

        foreach ($state->board->snakes as $snake) {
            if ($snake->id === $state->you->id || $snake->head === null || $snake->length >= $state->you->length) {
                continue;
            }
            $distance = \abs($snake->head->x - $head->x) + \abs($snake->head->y - $head->y);
            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $snake;
            }
        }

        return $nearest;
    }
}
