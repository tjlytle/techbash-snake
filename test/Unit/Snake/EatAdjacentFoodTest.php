<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Snake;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Snake\EatAdjacentFood;
use BattleSnake\Tests\SnekSpec\Parser;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EatAdjacentFoodTest extends TestCase
{
    private EatAdjacentFood $snake;

    protected function setUp(): void
    {
        $this->snake = new EatAdjacentFood(new MoveClassifier());
    }

    #[Test]
    public function takes_food_next_to_its_head(): void
    {
        $state = self::state(<<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            S0---------
            t----------
            t----------
            T----------
            -----------
            -----------
            BOARD);

        self::assertSame(Direction::RIGHT, ($this->snake)(TestGame::fresh(), $state));
    }

    #[Test]
    public function answers_a_move_that_is_not_safe_when_there_is_no_food_next_door(): void
    {
        $state = self::state(<<<'BOARD'
            -----------
            -----------
            -----------
            -----------
            -----------
            S------0---
            t----------
            t----------
            T----------
            -----------
            -----------
            BOARD);

        $direction = ($this->snake)(TestGame::fresh(), $state);

        foreach ((new MoveClassifier())($state) as $move) {
            if ($move->direction === $direction) {
                self::assertFalse($move->is(Classification::SAFE), 'With no food next door the answer must lose a Scored vote.');
                return;
            }
        }
        self::fail('The classifier did not label the returned direction.');
    }

    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse($board));
    }
}
