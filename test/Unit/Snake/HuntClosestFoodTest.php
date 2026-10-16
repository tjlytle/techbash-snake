<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Snake;

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Snake\HuntClosestFood;
use BattleSnake\Tests\SnekSpec\Parser as SnekSpecParser;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * HuntClosestFood alone, with no composition around it.
 */
final class HuntClosestFoodTest extends TestCase
{
    private HuntClosestFood $snake;

    protected function setUp(): void
    {
        $this->snake = new HuntClosestFood(new MoveClassifier());
    }

    private static function board(string $ascii): GameState
    {
        return GameStateParserFactory::make()->parse((new SnekSpecParser())->parse($ascii));
    }

    #[Test]
    public function goes_for_the_closer_of_two_foods(): void
    {
        $state = self::board(<<<EOT
        -----------
        -----0-----
        -----------
        -----------
        -----------
        -----S-0---
        -----t-----
        -----T-----
        -----------
        -----------
        -----------
        EOT);

        self::assertSame(Direction::RIGHT, ($this->snake)(TestGame::fresh(), $state));
    }

    #[Test]
    public function steps_around_what_is_in_the_way(): void
    {
        $state = self::board(<<<EOT
        -----------
        -----------
        -----------
        -0---------
        -----------
        StT--------
        -----------
        -----------
        -----------
        0----------
        -----------
        EOT);

        self::assertSame(Direction::UP, ($this->snake)(TestGame::fresh(), $state));
    }

    #[Test]
    public function knows_when_there_is_food_to_hunt(): void
    {
        $food = self::board(<<<EOT
        -----------
        -----------
        -----------
        -----------
        -----------
        -----S-0---
        -----t-----
        -----T-----
        -----------
        -----------
        -----------
        EOT);
        $none = self::board(<<<EOT
        -----------
        -----------
        -----------
        -----------
        -----------
        -----S-----
        -----t-----
        -----T-----
        -----------
        -----------
        -----------
        EOT);

        self::assertTrue(HuntClosestFood::hasFood($food));
        self::assertFalse(HuntClosestFood::hasFood($none));
    }
}
