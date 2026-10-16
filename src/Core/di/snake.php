<?php

declare(strict_types=1);

/**
 * The snake that plays, composed from small strategies:
 *
 *   Nudgeable                      a nudge from POST /manual wins when it is safe
 *     Conditional                  is there food on the board?
 *       yes: FirstSafe
 *              HuntClosestFood     head for the closest food
 *              RandomSnake         when the hunt has no safe move
 *       no:  RandomSnake           nothing to hunt, wander safely
 */

use BattleSnake\Analyzer\MoveClassifier;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Snake\HuntClosestFood;
use BattleSnake\Snake\RandomSnake;
use BattleSnake\Strategy\Aggregate\Conditional;
use BattleSnake\Strategy\Aggregate\FirstSafe;
use BattleSnake\Strategy\Decorator\Nudgeable;
use BattleSnake\Strategy\Strategy;
use Psr\Container\ContainerInterface;

return [
    Strategy::class => DI\get(Nudgeable::class),

    Nudgeable::class => DI\autowire()
        ->constructorParameter('fallback', DI\get('snake.hungry')),

    'snake.hungry' => static fn(ContainerInterface $c) => new Conditional(
        static fn(Game $game, GameState $state): int => HuntClosestFood::hasFood($state) ? 0 : 1,
        new FirstSafe(
            $c->get(MoveClassifier::class),
            $c->get(HuntClosestFood::class),
            $c->get(RandomSnake::class),
        ),
        $c->get(RandomSnake::class),
    ),

    HuntClosestFood::class => DI\autowire(),
    RandomSnake::class => DI\autowire(),
];
