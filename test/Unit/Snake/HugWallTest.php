<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Snake;

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Snake\HugWall;
use BattleSnake\Tests\SnekSpec\Parser;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HugWallTest extends TestCase
{
    private HugWall $snake;

    protected function setUp(): void
    {
        $this->snake = new HugWall(new MoveClassifier());
    }

    #[Test]
    public function heads_for_the_nearest_wall(): void
    {
        $state = self::state(<<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            ---Stt-----
            -----T-----
            -----------
            -----------
            -----------
            -----------
            BOARD);

        self::assertSame(Direction::LEFT, ($this->snake)(TestGame::fresh(), $state));
    }

    #[Test]
    public function keeps_its_direction_along_the_wall_when_both_ways_are_equal(): void
    {
        $state = self::state(<<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            SttT-------
            -----------
            -----------
            -----------
            -----------
            -----------
            BOARD);

        self::assertSame(Direction::UP, ($this->snake)(TestGame::afterMoves(Direction::UP), $state));
        self::assertSame(Direction::DOWN, ($this->snake)(TestGame::afterMoves(Direction::DOWN), $state));
    }

    #[Test]
    public function turns_back_toward_the_wall_after_being_pushed_off_it(): void
    {
        $state = self::state(<<<'BOARD'
            -----------
            -----------
            -----------
            UvvV-------
            tS---------
            t----------
            t----------
            T----------
            -----------
            -----------
            -----------
            BOARD);

        self::assertSame(Direction::DOWN, ($this->snake)(TestGame::afterMoves(Direction::RIGHT), $state));
    }

    #[Test]
    public function never_picks_a_wall_square_it_cannot_enter(): void
    {
        // Along the top wall, blocked ahead: the only non-END move is down, off the wall.
        $state = self::state(<<<'BOARD'
            -ttSUvvV---
            -T---------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            -----------
            BOARD);

        self::assertSame(Direction::DOWN, ($this->snake)(TestGame::afterMoves(Direction::RIGHT), $state));
    }

    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse($board));
    }
}
