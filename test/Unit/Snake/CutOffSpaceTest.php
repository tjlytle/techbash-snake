<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Snake;

use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Analyzer\OpenArea;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Snake\CutOffSpace;
use BattleSnake\Tests\SnekSpec\Parser;
use BattleSnake\Tests\Support\TestGame;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CutOffSpaceTest extends TestCase
{
    private CutOffSpace $snake;

    protected function setUp(): void
    {
        $this->snake = new CutOffSpace(new MoveClassifier(), new OpenArea());
    }

    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse(\trim($board)));
    }

    private function assertAbstains(GameState $state): void
    {
        $direction = ($this->snake)(TestGame::fresh(), $state);

        foreach ((new MoveClassifier())($state) as $move) {
            if ($move->direction === $direction) {
                self::assertTrue($move->is(Classification::END));
                return;
            }
        }
        self::fail('Direction not classified');
    }

    #[Test]
    public function seals_the_strip_a_shorter_snake_is_in(): void
    {
        $board = <<<'BOARD'
        --T--------
        --t--------
        --t--------
        Uvt--------
        -vt--------
        -Vt--------
        --t--------
        --t--------
        --t--------
        --S-0------
        -----------
        BOARD;

        self::assertSame(Direction::DOWN, ($this->snake)(TestGame::fresh(), self::state($board)));
    }

    #[Test]
    public function refuses_a_seal_that_leaves_us_less_room_than_our_length(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -----------
        -----------
        -----------
        -----------
        -----------
        ---tttttttT
        ---S---UvvV
        BOARD;

        // Right seals them into three squares but leaves us two; left leaves them three and us the board.
        self::assertSame(Direction::LEFT, ($this->snake)(TestGame::fresh(), self::state($board)));
    }

    #[Test]
    public function abstains_with_no_other_snake(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -----------
        --S--------
        --t--------
        --T--------
        -----------
        -----------
        -----------
        BOARD;

        $this->assertAbstains(self::state($board));
    }

    #[Test]
    public function abstains_with_no_safe_move(): void
    {
        $board = <<<'BOARD'
        -----------
        -----------
        -----------
        -----------
        -----------
        -----------
        -----------
        T----------
        t----------
        t----------
        S-UvvV-----
        BOARD;

        // Up is our neck, right is next to the head of a snake as long as us (a
        // shorter head would not make it DANGER), left and down are off the board.
        $this->assertAbstains(self::state($board));
    }
}
