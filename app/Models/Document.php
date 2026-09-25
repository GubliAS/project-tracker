<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;

#[Fillable(['name', 'file_path', 'category', 'size', 'project_id', 'user_id'])]
class Document extends Model
{
    use HasFactory;

    /**
     * @return list<string>
     */
    public static function uploadRules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,webp,svg,txt,csv,zip',
            'max:20480',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function uploadMessagesFor(string $attribute): array
    {
        return [
            "{$attribute}.mimes" => ':attribute is not an allowed file type. Use PDF, Word, Excel, PowerPoint, images, text, CSV, or zip.',
            "{$attribute}.max" => ':attribute is too large. Each file must be 20 MB or smaller.',
            "{$attribute}.file" => ':attribute is not a valid file.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function categoryRules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'in:planning,design,technical,financial,quality,other',
        ];
    }

    public static function storeUploaded(UploadedFile $file, ?int $projectId, string $category, ?int $userId): self
    {
        $path = $file->store('documents', 'public');

        return self::query()->create([
            'name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'category' => $category,
            'size' => $file->getSize(),
            'project_id' => $projectId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
