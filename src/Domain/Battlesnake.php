<?php

declare(strict_types=1);

namespace BattleSnake\Domain;

final readonly class Battlesnake
{
    public function __construct(
        public string $id,
        public string $name,
        public int $health,
        public CoordinateCollection $body = new CoordinateCollection(),
        public string $latency = '',
        public Coordinate|null $head = null,
        public int $length = 0,
        public string $shout = '',
        public string $squad = '',
        public Customizations $customizations = new Customizations(),
    ) {
    }

    /** True if any of this snake's body segments sit on $position. */
    public function occupies(Coordinate $position): bool
    {
        foreach ($this->body as $segment) {
            if ($segment->equals($position)) {
                return true;
            }
        }
        return false;
    }

    /**
     * True if this snake ate last turn: the rules stack a new segment on the
     * tail, so the last two body coordinates are the same square. Also true at
     * game start, when every segment is stacked.
     */
    public function justAte(): bool
    {
        $body = $this->body->coordinates;
        $count = \count($body);
        return $count >= 2 && $body[$count - 1]->equals($body[$count - 2]);
    }

    /**
     * True if this snake will still be on $position next turn: every segment
     * but the tail, which moves off its square unless the snake just ate.
     */
    public function blocks(Coordinate $position): bool
    {
        $body = $this->body->coordinates;
        if (! $this->justAte()) {
            \array_pop($body);
        }
        foreach ($body as $segment) {
            if ($segment->equals($position)) {
                return true;
            }
        }
        return false;
    }

    /** True if this snake's head could move to $position next turn. */
    public function couldReach(Coordinate $position): bool
    {
        if ($this->head === null) {
            return false;
        }
        foreach (Direction::cases() as $direction) {
            if ($this->head->adjacent($direction)->equals($position)) {
                return true;
            }
        }
        return false;
    }
}
