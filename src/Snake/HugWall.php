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
 * Runs around the outside of the board.
 *
 * Of the moves that are not END, pick the one whose square is closest to any
 * wall. On the wall that means "keep following it"; pushed off it, that means
 * "get back". Ties go to the direction we moved last, so the snake keeps
 * running round instead of wiggling. Nothing else: no food, no other heads.
 */
final class HugWall implements Strategy
{
    public function __construct(private readonly MoveClassifier $classifier)
    {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $head = $state->you->head;
        $board = $state->board;

        /** @var array<string, int> $distance direction value => squares to the nearest wall */
        $distance = [];
        foreach (($this->classifier)($state) as $move) {
            if ($move->is(Classification::END)) {
                continue;
            }
            $to = $head->adjacent($move->direction);
            $distance[$move->direction->value] = \min(
                $to->x,
                $to->y,
                $board->width - 1 - $to->x,
                $board->height - 1 - $to->y,
            );
        }

        if ($distance === []) {
            return Direction::UP;
        }

        $closest = \min($distance);
        $best = \array_keys($distance, $closest, true);

        $last = $game->last_move?->value;
        if ($last !== null && \in_array($last, $best, true)) {
            return Direction::from($last);
        }

        return Direction::from($best[0]);
    }
}
