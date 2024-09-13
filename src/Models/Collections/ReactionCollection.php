<?php

namespace Spatie\Comments\Models\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as IlluminateCollection;
use Spatie\Comments\Models\Concerns\Interfaces\CanComment;
use Spatie\Comments\Models\Reaction;

class ReactionCollection extends Collection
{
    /** @return IlluminateCollection<mixed> */
    public function summary(CanComment $commentator = null): IlluminateCollection
    {
        $commentator ??= auth()->user();

        $allowedReactions = config('comments.allowed_reactions');

        return $this
            ->groupBy('reaction')
            ->map(function (ReactionCollection $reactionCollection, string $reaction) {
                return [
                    'reaction' => $reaction,
                    'count' => $reactionCollection->count(),
                ];
            })
            ->sortBy(function (array $summary) use ($allowedReactions) {
                return array_search($summary['reaction'], $allowedReactions) ?? PHP_INT_MAX;
            })
            ->map(function (array $summary) use ($commentator) {
                $currentCommentatorReacted = $this->contains(function (Reaction $reactionInCollection) use ($commentator, $summary) {
                    if ($summary['reaction'] !== $reactionInCollection->reaction) {
                        return false;
                    }

                    if (! $commentator) {
                        return false;
                    }

                    if ($commentator->getMorphClass() !== $reactionInCollection->commentator_type) {
                        return false;
                    }

                    if ($commentator->getKey() !== $reactionInCollection->commentator_id) {
                        return false;
                    }

                    return true;
                });

                return array_merge($summary, [
                    'commentator_reacted' => $currentCommentatorReacted,
                ]);
            })
            ->values();
    }
}
