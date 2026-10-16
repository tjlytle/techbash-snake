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

    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse(\trim($board)));
    }

    #[Test]
    public function takes_food_next_to_the_head(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -----------
        -0S--------
        --t--------
        --t--------
        --T--------
        -----------
        -----------
        BOARD;

        self::assertSame(Direction::LEFT, ($this->snake)(TestGame::fresh(), self::state($board)));
    }

    #[Test]
    public function ignores_food_that_is_not_adjacent(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -----------
        --S---0----
        --t--------
        --t--------
        --T--------
        -----------
        -----------
        BOARD;

        $state = self::state($board);
        $direction = ($this->snake)(TestGame::fresh(), $state);

        // With nothing to eat it abstains by voting a dead square, so an aggregate never picks it.
        foreach ((new MoveClassifier())($state) as $move) {
            if ($move->direction === $direction) {
                self::assertTrue($move->is(Classification::END));
                return;
            }
        }
        self::fail('Direction not classified');
    }

    #[Test]
    public function leaves_food_an_enemy_head_can_also_reach(): void
    {
        // The enemy is as long as us: a shorter one would not make the food DANGER.
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -----------
        U0S--------
        v-t--------
        v-t--------
        V-T--------
        -----------
        -----------
        BOARD;

        self::assertNotSame(Direction::LEFT, ($this->snake)(TestGame::fresh(), self::state($board)));
    }
}
