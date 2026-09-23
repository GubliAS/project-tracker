<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'workspace_id',
    'name',
    'description',
    'status',
    'team',
    'client',
    'priority',
    'project_type',
    'start_date',
    'end_date',
    'budget',
    'spent',
    'currency',
    'settings',
])]
class Project extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
            'spent' => 'decimal:2',
            'currency' => Currency::class,
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return HasMany<QualityCheck, $this>
     */
    public function qualityChecks(): HasMany
    {
        return $this->hasMany(QualityCheck::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function lessonsLearned(): HasMany
    {
        return $this->hasMany(LessonLearned::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function kickoffs(): HasMany
    {
        return $this->hasMany(Kickoff::class);
    }

    public function stakeholders(): HasMany
    {
        return $this->hasMany(Stakeholder::class);
    }

    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }

    public function backlogItems(): HasMany
    {
        return $this->hasMany(BacklogItem::class);
    }

    public function definitionItems(): HasMany
    {
        return $this->hasMany(DefinitionItem::class);
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function progressPercent(): int
    {
        $tasks = $this->relationLoaded('tasks')
            ? $this->tasks
            : $this->tasks()->get(['id', 'status', 'weight']);

        $totalWeight = (int) $tasks->sum(fn (Task $task): int => $this->normalizedTaskWeight($task->weight));

        if ($totalWeight === 0) {
            return 0;
        }

        $doneWeight = (int) $tasks
            ->where('status', 'done')
            ->sum(fn (Task $task): int => $this->normalizedTaskWeight($task->weight));

        return (int) round(($doneWeight / $totalWeight) * 100);
    }

    private function normalizedTaskWeight(mixed $weight): int
    {
        if ($weight === null || $weight === '') {
            return 1;
        }

        $value = (int) $weight;

        return $value > 0 ? $value : 1;
    }
}
