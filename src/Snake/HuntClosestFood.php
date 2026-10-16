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
 * Heads for the closest food on the board (Manhattan distance from our head):
 *
 *   - of the SAFE moves, take the one that lands nearest that food
 *   - with no SAFE move, the nearest non-END move (the composition decides what to do with it)
 *
 * It only knows about food. Compose it behind `hasFood()` for the empty board, and behind
 * FirstSafe so an unsafe answer goes to another strategy.
 */
final class HuntClosestFood implements Strategy
{
    public function __construct(private readonly MoveClassifier $classifier)
    {
    }

    public static function hasFood(GameState $state): bool
    {
        return $state->board->food->coordinates !== [];
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $head = $state->you->head;
        if ($head === null) {
            return Direction::UP;
        }

        $target = null;
        foreach ($state->board->food as $food) {
            if ($target === null || self::distance($head, $food) < self::distance($head, $target)) {
                $target = $food;
            }
        }

        if ($target === null) {
            return Direction::UP;
        }

        $safe = [];
        $any = [];
        foreach (($this->classifier)($state) as $move) {
            if ($move->is(Classification::END)) {
                continue;
            }
            $distance = self::distance($head->adjacent($move->direction), $target);
            $any[$move->direction->value] = $distance;
            if ($move->is(Classification::SAFE)) {
                $safe[$move->direction->value] = $distance;
            }
        }

        $pool = $safe !== [] ? $safe : $any;
        if ($pool === []) {
            return Direction::UP;
        }

        \asort($pool);
        return Direction::from((string) \array_key_first($pool));
    }

    private static function distance(Coordinate $a, Coordinate $b): int
    {
        return \abs($a->x - $b->x) + \abs($a->y - $b->y);
    }
}
