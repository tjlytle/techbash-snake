<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Snake;

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Analyzer\OpenArea;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Snake\AvoidDeadEnds;
use BattleSnake\Strategy\Strategy;
use BattleSnake\Tests\SnekSpec\Parser;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AvoidDeadEndsTest extends TestCase
{
    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse(\trim($board)));
    }

    private static function preferring(Direction $direction): AvoidDeadEnds
    {
        $inner = new class ($direction) implements Strategy {
            public function __construct(private readonly Direction $direction)
            {
            }

            public function __invoke(Game $game, GameState $state): Direction
            {
                return $this->direction;
            }
        };

        return new AvoidDeadEnds($inner, new MoveClassifier(), new OpenArea());
    }

    /** QA's solo game 7ebdae57, turn 163: right is the one-square corner, left is the open top row. */
    private const string CORNER = <<<'BOARD'
    ---------S-
    -------tttt
    ------ttttt
    ------t-ttt
    ------t-ttt
    ------ttttt
    ----------t
    ----------T
    -----------
    -----------
    ----------0
    BOARD;

    #[Test]
    public function refuses_a_move_into_a_pocket_smaller_than_our_length(): void
    {
        self::assertSame(Direction::LEFT, (self::preferring(Direction::RIGHT))(TestGame::fresh(), self::state(self::CORNER)));
    }

    #[Test]
    public function keeps_a_move_that_leaves_room(): void
    {
        self::assertSame(Direction::LEFT, (self::preferring(Direction::LEFT))(TestGame::fresh(), self::state(self::CORNER)));
    }

    #[Test]
    public function leaves_an_abstaining_vote_alone(): void
    {
        // Up is off the board: an END vote is how a strategy abstains, so it passes through.
        self::assertSame(Direction::UP, (self::preferring(Direction::UP))(TestGame::fresh(), self::state(self::CORNER)));
    }
}
