<?php

declare(strict_types=1);

/**
 * The TechBash snake is a composition of small strategies, not one class.
 * Each behaviour is its own Strategy under src/Snake/; this file is the only
 * place that decides how they combine.
 */

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Snake\CircleTheRing;
use BattleSnake\Snake\CutOffSpace;
use BattleSnake\Snake\EatAdjacentFood;
use BattleSnake\Snake\HuntFood;
use BattleSnake\Snake\RandomSnake;
use BattleSnake\Strategy\Aggregate\Conditional;
use BattleSnake\Strategy\Aggregate\Scored;
use BattleSnake\Strategy\Decorator\Fallback;
use BattleSnake\Strategy\Decorator\Nudgeable;
use BattleSnake\Strategy\Strategy;
use Psr\Container\ContainerInterface;

return [
    Strategy::class => DI\get(Nudgeable::class),

    // POST /manual can nudge any move; otherwise the composition below plays.
    Nudgeable::class => DI\autowire()
        ->constructorParameter('fallback', DI\get(Conditional::class)),

    // Conditional: grow until we are strictly longer than every other snake, then trap.
    // Equal length is not big (a head-to-head kills both); a solo game always grows.
    //   0. grow - Scored(HuntFood, EatAdjacentFood, RandomSnake)
    //   1. trap - Fallback(CutOffSpace, RandomSnake); no food strategy here, the trap does not eat
    // CutOffSpace replaced CircleTheRing, which stays in place and unwired.
    Conditional::class => DI\factory(static fn(ContainerInterface $c): Conditional => new Conditional(
        static function (Game $game, GameState $state): int {
            $others = 0;
            foreach ($state->board->snakes as $snake) {
                if ($snake->id === $state->you->id) {
                    continue;
                }
                if ($snake->length >= $state->you->length) {
                    return 0;
                }
                $others++;
            }
            return $others > 0 ? 1 : 0;
        },
        $c->get(Scored::class),
        $c->get(Fallback::class),
    )),

    // Scored: every strategy votes a direction, the best classifier label wins
    // (SAFE+FOOD > SAFE > DANGER > END), ties go to declaration order. So:
    //   1. HuntFood        - the SAFE move nearest to food; wins by default
    //   2. EatAdjacentFood - food next to the head outranks a plain SAFE vote
    //   3. RandomSnake     - the safety net: any SAFE square when both abstain
    Scored::class => DI\factory(static fn(ContainerInterface $c): Scored => new Scored(
        $c->get(MoveClassifier::class),
        $c->get(HuntFood::class),
        $c->get(EatAdjacentFood::class),
        $c->get(RandomSnake::class),
    )),

    // Fallback: CutOffSpace abstains with an END vote when nothing qualifies; RandomSnake then picks a SAFE square.
    Fallback::class => DI\factory(static fn(ContainerInterface $c): Fallback => new Fallback(
        $c->get(CutOffSpace::class),
        $c->get(RandomSnake::class),
        $c->get(MoveClassifier::class),
    )),

    CircleTheRing::class => DI\autowire(),
    CutOffSpace::class => DI\autowire(),
    EatAdjacentFood::class => DI\autowire(),
    HuntFood::class => DI\autowire(),
    RandomSnake::class => DI\autowire(),
];
