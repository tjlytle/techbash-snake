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
 * Eats food that is next to the head, and only that. It never hunts.
 *
 * With nothing to eat it abstains: it votes for a square the MoveClassifier
 * labels END, so a Scored or FirstSafe aggregate always prefers another vote.
 * (There is always one after turn 0: our own neck.) If somehow nothing is END,
 * it votes UP, which at worst ties and loses on declaration order.
 */
final class EatAdjacentFood implements Strategy
{
    public function __construct(private readonly MoveClassifier $classifier)
    {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $dead = null;

        foreach (($this->classifier)($state) as $move) {
            if ($move->is(Classification::SAFE) && $move->is(Classification::FOOD)) {
                return $move->direction;
            }
            if ($move->is(Classification::END)) {
                $dead ??= $move->direction;
            }
        }

        return $dead ?? Direction::UP;
    }
}
