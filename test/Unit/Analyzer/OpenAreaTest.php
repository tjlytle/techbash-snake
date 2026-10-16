<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Analyzer;

use BattleSnake\Analyzer\OpenArea;
use BattleSnake\Domain\Coordinate;
use BattleSnake\Domain\GameState;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Tests\SnekSpec\Parser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OpenAreaTest extends TestCase
{
    private static function state(string $board): GameState
    {
        return GameStateParserFactory::make()->parse((new Parser())->parse(\trim($board)));
    }

    #[Test]
    public function counts_every_free_square_on_an_open_board(): void
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

        $state = self::state($board);

        // 121 squares, 3 of them snake; the start square (the head) is not counted either way.
        self::assertSame(118, (new OpenArea())($state->board, $state->you->head));
    }

    #[Test]
    public function counts_only_the_pocket_a_head_is_walled_into(): void
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

        $state = self::state($board);
        $theirs = $state->board->snakes->battlesnakes[1]->head;

        // (4,0), (5,0), (6,0): the strip between our head and theirs.
        self::assertSame(3, (new OpenArea())($state->board, $theirs));
    }

    #[Test]
    public function an_extra_blocked_square_closes_the_pocket(): void
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

        $state = self::state($board);
        $theirs = $state->board->snakes->battlesnakes[1]->head;

        self::assertSame(1, (new OpenArea())($state->board, $theirs, new Coordinate(5, 0)));
    }
}
