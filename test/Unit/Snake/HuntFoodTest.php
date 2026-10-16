<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Snake;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Snake\HuntFood;
use BattleSnake\Tests\SnekSpec\Parser;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HuntFoodTest extends TestCase
{
    private HuntFood $snake;

    protected function setUp(): void
    {
        $this->snake = new HuntFood(new MoveClassifier());
    }

    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse(\trim($board)));
    }

    #[Test]
    public function heads_for_food_two_squares_away(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        ---0-------
        -----------
        ---S-------
        ---t---UvvV
        ---T-------
        -----------
        -----------
        -----------
        BOARD;

        self::assertSame(Direction::UP, ($this->snake)(TestGame::fresh(), self::state($board)));
    }

    #[Test]
    public function goes_round_a_body_between_the_head_and_the_food(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----0-----
        ---VvvvU---
        -----S-----
        -----t-----
        -----T-----
        -----------
        -----------
        -----------
        BOARD;

        $direction = ($this->snake)(TestGame::fresh(), self::state($board));

        self::assertContains($direction, [Direction::LEFT, Direction::RIGHT]);
    }

    #[Test]
    public function never_takes_a_danger_square_to_get_closer(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----0-----
        ------UvvV-
        -----S-----
        -----t-----
        -----T-----
        -----------
        -----------
        -----------
        BOARD;

        // Up is one from the food but next to their head; right is too. Left is the SAFE way.
        self::assertSame(Direction::LEFT, ($this->snake)(TestGame::fresh(), self::state($board)));
    }

    #[Test]
    public function abstains_when_there_is_no_food(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -----------
        ---S-------
        ---t---UvvV
        ---T-------
        -----------
        -----------
        -----------
        BOARD;

        $state = self::state($board);
        $direction = ($this->snake)(TestGame::fresh(), $state);

        foreach ((new MoveClassifier())($state) as $move) {
            if ($move->direction === $direction) {
                self::assertTrue($move->is(Classification::END));
                return;
            }
        }
        self::fail('Direction not classified');
    }
}
