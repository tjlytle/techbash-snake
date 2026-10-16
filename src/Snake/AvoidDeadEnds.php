<?php

declare(strict_types=1);

namespace BattleSnake\Snake;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Analyzer\OpenArea;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Strategy;

/**
 * Refuses dead ends: asks the strategy it wraps for a move, and if that move
 * leaves our head fewer reachable squares than our length, takes the SAFE move
 * with the most room instead. Ties go to the wrapped strategy's choice.
 *
 * An END vote is how a strategy abstains, so it passes through untouched, and
 * so does any move when no SAFE move has more room.
 */
final class AvoidDeadEnds implements Strategy
{
    public function __construct(
        private readonly Strategy $inner,
        private readonly MoveClassifier $classifier,
        private readonly OpenArea $openArea,
    ) {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $direction = ($this->inner)($game, $state);
        $head = $state->you->head;
        if ($head === null) {
            return $direction;
        }

        $room = [];
        foreach (($this->classifier)($state) as $move) {
            if ($move->direction === $direction && $move->is(Classification::END)) {
                return $direction;
            }
            if ($move->direction === $direction || $move->is(Classification::SAFE)) {
                $room[$move->direction->value] = ($this->openArea)($state->board, $head->adjacent($move->direction));
            }
        }

        $chosen = $room[$direction->value] ?? 0;
        if ($chosen >= $state->you->length) {
            return $direction;
        }

        $best = $direction;
        foreach ($room as $value => $area) {
            if ($area > $chosen) {
                $chosen = $area;
                $best = Direction::from($value);
            }
        }

        return $best;
    }
}
