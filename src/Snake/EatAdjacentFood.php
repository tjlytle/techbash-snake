<?php

declare(strict_types=1);

namespace BattleSnake\Snake;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Strategy;

/**
 * Takes food that is right next to the head, when the move is SAFE.
 *
 * The aggregates have no "abstain", so with no food next door this answers
 * its worst move (an END if there is one, else anything not SAFE) and lets a
 * Scored aggregate vote it out. Meant to sit beside a movement strategy, never
 * to play alone.
 */
final class EatAdjacentFood implements Strategy
{
    public function __construct(private readonly MoveClassifier $classifier)
    {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $worst = null;
        $worst_rank = \PHP_INT_MAX;

        foreach (($this->classifier)($state) as $move) {
            if ($move->is(Classification::SAFE) && $move->is(Classification::FOOD)) {
                return $move->direction;
            }

            $rank = match (true) {
                $move->is(Classification::END) => 0,
                ! $move->is(Classification::SAFE) => 1,
                default => 2,
            };
            if ($rank < $worst_rank) {
                $worst_rank = $rank;
                $worst = $move->direction;
            }
        }

        return $worst ?? Direction::UP;
    }
}
