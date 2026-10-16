<?php

declare(strict_types=1);

namespace BattleSnake\Snake;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Coordinate;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Strategy;

/**
 * Hunts food: among the SAFE moves, the one whose square is nearest
 * (Manhattan) to any food on the board. Ties go to the first direction found.
 *
 * With no food or no SAFE move it abstains the way EatAdjacentFood does: it
 * votes a square the MoveClassifier labels END, so an aggregate prefers
 * another vote. It does not look for dead ends; that is another strategy's job.
 */
final class HuntFood implements Strategy
{
    public function __construct(private readonly MoveClassifier $classifier)
    {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $head = $state->you->head;
        $best = null;
        $bestDistance = \PHP_INT_MAX;
        $dead = null;

        foreach (($this->classifier)($state) as $move) {
            if ($move->is(Classification::END)) {
                $dead ??= $move->direction;
                continue;
            }
            if ($head === null || ! $move->is(Classification::SAFE)) {
                continue;
            }

            $square = $head->adjacent($move->direction);
            foreach ($state->board->food as $food) {
                $distance = self::distance($square, $food);
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $best = $move->direction;
                }
            }
        }

        return $best ?? $dead ?? Direction::UP;
    }

    private static function distance(Coordinate $a, Coordinate $b): int
    {
        return \abs($a->x - $b->x) + \abs($a->y - $b->y);
    }
}
