<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Snake;

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Snake\CircleTheRing;
use BattleSnake\Tests\SnekSpec\Parser;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The ring on an 11x11 board is columns and rows 2 and 8 (a quarter of the
 * board in from each wall). The snake follows it clockwise: up the left side,
 * right along the top, down the right side, left along the bottom.
 */
final class CircleTheRingTest extends TestCase
{
    private CircleTheRing $snake;

    protected function setUp(): void
    {
        $this->snake = new CircleTheRing(new MoveClassifier());
    }

    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse(\trim($board)));
    }

    /** @return iterable<string, array{string, Direction}> */
    public static function ringPositions(): iterable
    {
        yield 'left side goes up' => [
            <<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            --S--------
            --t--------
            --t--------
            --T--------
            -----------
            -----------
            BOARD,
            Direction::UP,
        ];
        yield 'top left corner turns right' => [
            <<<'BOARD'
            -----------
            -----------
            --S--------
            --t--------
            --t--------
            --T--------
            -----------
            -----------
            -----------
            -----------
            -----------
            BOARD,
            Direction::RIGHT,
        ];
        yield 'top goes right' => [
            <<<'BOARD'
            -----------
            -----------
            --tttS-----
            --T--------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            BOARD,
            Direction::RIGHT,
        ];
        yield 'top right corner turns down' => [
            <<<'BOARD'
            -----------
            -----------
            -----tttS--
            -----T-----
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            BOARD,
            Direction::DOWN,
        ];
        yield 'right side goes down' => [
            <<<'BOARD'
            -----------
            -----------
            --------T--
            --------t--
            --------t--
            --------S--
            -----------
            -----------
            -----------
            -----------
            -----------
            BOARD,
            Direction::DOWN,
        ];
        yield 'bottom right corner turns left' => [
            <<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            --------T--
            --------t--
            --------t--
            --------S--
            -----------
            -----------
            BOARD,
            Direction::LEFT,
        ];
        yield 'bottom goes left' => [
            <<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            ----Sttt---
            -------T---
            -----------
            BOARD,
            Direction::LEFT,
        ];
        yield 'bottom left corner turns up' => [
            <<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            --Sttt-----
            -----T-----
            -----------
            BOARD,
            Direction::UP,
        ];
    }

    #[Test]
    #[DataProvider('ringPositions')]
    public function follows_the_ring_clockwise(string $board, Direction $expected): void
    {
        self::assertSame($expected, ($this->snake)(TestGame::fresh(), self::state($board)));
    }

    /** @return iterable<string, array{string, Direction}> */
    public static function offRingPositions(): iterable
    {
        yield 'outside on the left heads right' => [
            <<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            S----------
            t----------
            t----------
            T----------
            -----------
            -----------
            BOARD,
            Direction::RIGHT,
        ];
        yield 'outside above heads down' => [
            <<<'BOARD'
            -----Sttt--
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            BOARD,
            Direction::DOWN,
        ];
        yield 'inside near the right side heads right' => [
            <<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            ----tttS---
            ----T------
            -----------
            -----------
            -----------
            -----------
            BOARD,
            Direction::RIGHT,
        ];
        yield 'inside near the bottom heads down' => [
            <<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            ----T------
            ----t------
            ----tS-----
            -----------
            -----------
            -----------
            -----------
            BOARD,
            Direction::DOWN,
        ];
    }

    #[Test]
    #[DataProvider('offRingPositions')]
    public function heads_for_the_ring_when_off_it(string $board, Direction $expected): void
    {
        self::assertSame($expected, ($this->snake)(TestGame::fresh(), self::state($board)));
    }

    #[Test]
    public function cuts_inward_when_the_ring_ahead_is_blocked(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -VvvvU-----
        --S--------
        --t--------
        --t--------
        --T--------
        -----------
        -----------
        BOARD;

        self::assertSame(Direction::RIGHT, ($this->snake)(TestGame::fresh(), self::state($board)));
    }

    #[Test]
    public function still_votes_for_the_ring_when_inward_is_blocked_too(): void
    {
        // Ahead and inward are both dead; this strategy has no third opinion.
        // It votes the ring anyway and lets the composition's safety net win.
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -VvvvU-----
        --SXxW-----
        --t--------
        --t--------
        --T--------
        -----------
        -----------
        BOARD;

        self::assertSame(Direction::UP, ($this->snake)(TestGame::fresh(), self::state($board)));
    }
}
