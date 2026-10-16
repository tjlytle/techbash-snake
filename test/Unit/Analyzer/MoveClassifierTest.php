<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Analyzer;

use BattleSnake\Analyzer\Move;
use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Tests\SnekSpec\Parser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A head-to-head is only lost against a snake as long as us or longer, so a
 * square only strictly shorter heads can reach is not DANGER.
 */
final class MoveClassifierTest extends TestCase
{
    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse(\trim($board)));
    }

    private static function move(GameState $state, Direction $direction): Move
    {
        foreach ((new MoveClassifier())($state) as $move) {
            if ($move->direction === $direction) {
                return $move;
            }
        }
        self::fail("No move {$direction->value}");
    }

    #[Test]
    public function a_square_only_a_shorter_head_can_reach_is_safe(): void
    {
        // We are length 4, U is length 3.
        $state = self::state(<<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        ---UvV-----
        --S--------
        --t--------
        --t--------
        --T--------
        -----------
        -----------
        BOARD);

        $up = self::move($state, Direction::UP);
        self::assertFalse($up->is(Classification::DANGER));
        self::assertTrue($up->is(Classification::SAFE));
    }

    #[Test]
    public function a_square_an_equal_length_head_can_reach_is_danger(): void
    {
        // We are length 4, U is length 4.
        $state = self::state(<<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        ---UvvV----
        --S--------
        --t--------
        --t--------
        --T--------
        -----------
        -----------
        BOARD);

        self::assertTrue(self::move($state, Direction::UP)->is(Classification::DANGER));
    }

    #[Test]
    public function one_head_as_long_as_us_keeps_a_square_danger(): void
    {
        // W (length 4) and U (length 3) both reach up; only U reaches right.
        $state = self::state(<<<'BOARD'
        --X--------
        --x--------
        --x--------
        --W--------
        ---UvV-----
        --S--------
        --t--------
        --t--------
        --T--------
        -----------
        -----------
        BOARD);

        self::assertTrue(self::move($state, Direction::UP)->is(Classification::DANGER));
        self::assertTrue(self::move($state, Direction::RIGHT)->is(Classification::SAFE));
    }
}
