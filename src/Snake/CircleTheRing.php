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
 * Follows a ring a quarter of the board in from each wall (columns and rows
 * 2 and 8 on an 11x11 board), clockwise: up the left side, right along the
 * top, down the right side, left along the bottom. The body it leaves behind
 * is the wall other snakes are meant to run into.
 *
 *   - off the ring: step toward it (outside: back in; inside: to the nearest edge)
 *   - on the ring: the next square clockwise
 *   - that square not SAFE: cut the corner, i.e. the inward (right-hand) turn
 *   - that blocked too: vote the ring anyway and let the composition's safety net win
 *
 * One concern only. Food is EatAdjacentFood's job; survival is the aggregate's.
 */
final class CircleTheRing implements Strategy
{
    public function __construct(private readonly MoveClassifier $classifier)
    {
    }

    public function __invoke(Game $game, GameState $state): Direction
    {
        $safe = [];
        foreach (($this->classifier)($state) as $move) {
            $safe[$move->direction->value] = $move->is(Classification::SAFE);
        }

        $along = $this->alongTheRing($state);
        if ($safe[$along->value] ?? false) {
            return $along;
        }

        $inward = self::rightTurn($along);
        if ($safe[$inward->value] ?? false) {
            return $inward;
        }

        return $along;
    }

    private function alongTheRing(GameState $state): Direction
    {
        $board = $state->board;
        $head = $state->you->head;
        if ($head === null) {
            return Direction::UP;
        }

        $inset = \intdiv(\min($board->width, $board->height), 4);
        $left = $inset;
        $right = $board->width - 1 - $inset;
        $bottom = $inset;
        $top = $board->height - 1 - $inset;
        $x = $head->x;
        $y = $head->y;

        // Outside the ring: step back toward it.
        if ($x < $left) {
            return Direction::RIGHT;
        }
        if ($x > $right) {
            return Direction::LEFT;
        }
        if ($y < $bottom) {
            return Direction::UP;
        }
        if ($y > $top) {
            return Direction::DOWN;
        }

        // On the ring: clockwise. Each side stops one short of its corner so the corner
        // square belongs to the next side.
        if ($x === $left && $y < $top) {
            return Direction::UP;
        }
        if ($y === $top && $x < $right) {
            return Direction::RIGHT;
        }
        if ($x === $right && $y > $bottom) {
            return Direction::DOWN;
        }
        if ($y === $bottom && $x > $left) {
            return Direction::LEFT;
        }

        // Inside the ring: head for the nearest side. Ties go to UP.
        $gaps = [
            Direction::UP->value => $top - $y,
            Direction::RIGHT->value => $right - $x,
            Direction::DOWN->value => $y - $bottom,
            Direction::LEFT->value => $x - $left,
        ];

        return Direction::from((string) \array_search(\min($gaps), $gaps, true));
    }

    /** The right-hand turn from a heading, which on a clockwise loop is the inward side. */
    private static function rightTurn(Direction $heading): Direction
    {
        return match ($heading) {
            Direction::UP => Direction::RIGHT,
            Direction::RIGHT => Direction::DOWN,
            Direction::DOWN => Direction::LEFT,
            Direction::LEFT => Direction::UP,
        };
    }
}
