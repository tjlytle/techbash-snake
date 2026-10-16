<?php

declare(strict_types=1);

/**
 * The TechBash snake is a composition of small strategies, not one class.
 * Each behaviour is its own Strategy under src/Snake/; this file is the only
 * place that decides how they combine.
 */

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Snake\CircleTheRing;
use BattleSnake\Snake\EatAdjacentFood;
use BattleSnake\Snake\RandomSnake;
use BattleSnake\Strategy\Aggregate\Scored;
use BattleSnake\Strategy\Decorator\Nudgeable;
use BattleSnake\Strategy\Strategy;
use Psr\Container\ContainerInterface;

return [
    Strategy::class => DI\get(Nudgeable::class),

    // POST /manual can nudge any move; otherwise the composition below plays.
    Nudgeable::class => DI\autowire()
        ->constructorParameter('fallback', DI\get(Scored::class)),

    // Scored: every strategy votes a direction, the best classifier label wins
    // (SAFE+FOOD > SAFE > DANGER > END), ties go to declaration order. So:
    //   1. CircleTheRing  - the loop itself; wins by default
    //   2. EatAdjacentFood - food next to the head outranks the loop (SAFE+FOOD beats SAFE)
    //   3. RandomSnake     - the safety net: any SAFE square when the loop would kill us
    Scored::class => DI\factory(static fn(ContainerInterface $c): Scored => new Scored(
        $c->get(MoveClassifier::class),
        $c->get(CircleTheRing::class),
        $c->get(EatAdjacentFood::class),
        $c->get(RandomSnake::class),
    )),

    CircleTheRing::class => DI\autowire(),
    EatAdjacentFood::class => DI\autowire(),
    RandomSnake::class => DI\autowire(),
];
