<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\Analyzer;

use BattleSnake\Analyzer\Move;
use BattleSnake\Analyzer\Move\Classification;
use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\Direction;
use BattleSnake\Domain\Parser\GameStateParserFactory;
use BattleSnake\Tests\SnekSpec\Parser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A tail leaves its square next turn, unless its snake just ate: then the
 * rules stack a segment on the tail and it stays put for one turn. Behat
 * boards cannot draw a stacked tail, so these build the request by hand.
 */
final class MoveClassifierTest extends TestCase
{
    // U's tail V is above our head; U itself is too far away to reach it.
    private const string OTHER_TAIL_ABOVE = <<<'BOARD'
        -------
        -Uvv---
        ---V---
        ---S---
        ---t---
        ---T---
        -------
        BOARD;

    // Our own tail is above our head, in a tight loop.
    private const string OWN_TAIL_ABOVE = <<<'BOARD'
        -------
        -------
        --Tt---
        --St---
        -------
        -------
        -------
        BOARD;

    /**
     * @param callable(array<string, mixed>): array<string, mixed> $edit
     */
    private static function move(string $board, Direction $direction, callable|null $edit = null): Move
    {
        $request = (new Parser())->parse($board);
        if ($edit !== null) {
            $request = $edit($request);
        }
        $state = GameStateParserFactory::make()->parse($request);

        foreach ((new MoveClassifier())($state) as $move) {
            if ($move->direction === $direction) {
                return $move;
            }
        }
        self::fail("No move {$direction->value}");
    }

    /** Duplicate the last body segment of snake $index, as the rules do when it eats. */
    private static function ate(int $index): callable
    {
        return static function (array $request) use ($index): array {
            $body = &$request['board']['snakes'][$index]['body'];
            $body[] = \end($body);
            if ($index === 0) {
                $request['you'] = $request['board']['snakes'][0];
            }
            return $request;
        };
    }

    #[Test]
    public function another_snakes_tail_is_safe(): void
    {
        $move = self::move(self::OTHER_TAIL_ABOVE, Direction::UP);

        self::assertTrue($move->is(Classification::SAFE));
        self::assertFalse($move->is(Classification::END));
    }

    #[Test]
    public function another_snakes_tail_is_end_when_it_just_ate(): void
    {
        $move = self::move(self::OTHER_TAIL_ABOVE, Direction::UP, self::ate(1));

        self::assertTrue($move->is(Classification::END));
    }

    #[Test]
    public function our_own_tail_is_safe(): void
    {
        $move = self::move(self::OWN_TAIL_ABOVE, Direction::UP);

        self::assertTrue($move->is(Classification::SAFE));
        self::assertFalse($move->is(Classification::END));
    }

    #[Test]
    public function our_own_tail_is_end_when_we_just_ate(): void
    {
        $move = self::move(self::OWN_TAIL_ABOVE, Direction::UP, self::ate(0));

        self::assertTrue($move->is(Classification::END));
    }

    #[Test]
    public function a_snake_at_game_start_blocks_its_square(): void
    {
        // U is a fresh snake with all three segments on one square, right of our head.
        $move = self::move(<<<'BOARD'
            -------
            -------
            -------
            ---SU--
            ---t---
            ---T---
            -------
            BOARD, Direction::RIGHT, static function (array $request): array {
                $head = $request['board']['snakes'][1]['head'];
                $request['board']['snakes'][1]['body'] = [$head, $head, $head];
                $request['board']['snakes'][1]['length'] = 3;
                return $request;
            });

        self::assertTrue($move->is(Classification::END));
    }
}
