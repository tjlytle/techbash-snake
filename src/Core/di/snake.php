<?php

declare(strict_types=1);

/**
 * The playing snake is a composition of small strategies, wired here and only here.
 *
 *   Nudgeable                       a /manual nudge wins when it is safe
 *     └─ Scored                     scores every answer with the classifier; SAFE+FOOD beats SAFE,
 *          ├─ HugWall               ties go to the first listed, so the wall wins unless food is beside us
 *          └─ EatAdjacentFood       only speaks up for food next to the head
 *
 * RandomSnake is the platform sample; HugWall + EatAdjacentFood replaced it as the fallback.
 */

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Snake\EatAdjacentFood;
use BattleSnake\Snake\HugWall;
use BattleSnake\Snake\RandomSnake;
use BattleSnake\Strategy\Aggregate\Scored;
use BattleSnake\Strategy\Decorator\Nudgeable;
use BattleSnake\Strategy\Strategy;

return [
    Strategy::class => DI\get(Nudgeable::class),

    Nudgeable::class => DI\autowire()
        ->constructorParameter('fallback', DI\get(Scored::class)),

    // A closure, not autowire: PHP-DI hands a variadic constructor one value and drops the rest.
    Scored::class => DI\factory(
        static fn(MoveClassifier $classifier, HugWall $wall, EatAdjacentFood $food): Scored
            => new Scored($classifier, $wall, $food),
    ),

    HugWall::class => DI\autowire(),
    EatAdjacentFood::class => DI\autowire(),

    // Superseded by the composition above; kept as the platform sample.
    RandomSnake::class => DI\autowire(),
];
