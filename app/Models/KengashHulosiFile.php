<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class KengashHulosiFile extends Model
{
    use HasFactory;

    protected $table = 'kengash_hulosasi_files';

    protected $fillable = [
        'kengash_hulosasi_id',
        'file_name',
        'original_name',
        'file_path',
        'file_size',
        'file_type',
        'file_date',
        'comment',
        'uploaded_by'
    ];

    protected $casts = [
        'file_date' => 'date',
    ];

    /**
     * Get the kengash hulosasi that owns the file.
     */
    public function kengashHulosasi(): BelongsTo
    {
        return $this->belongsTo(KengashHulosasi::class, 'kengash_hulosasi_id');
    }

    /**
     * Get the user who uploaded the file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' ГБ';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' МБ';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' КБ';
        } else {
            return $bytes . ' байт';
        }
    }

    /**
     * Get file extension
     */
    public function getExtensionAttribute(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    /**
     * Check if file is image
     */
    public function isImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        return in_array(strtolower($this->extension), $imageExtensions);
    }

    /**
     * Check if file is document
     */
    public function isDocument(): bool
    {
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        return in_array(strtolower($this->extension), $documentExtensions);
    }

    /**
     * Get file icon class
     */
    public function getIconClassAttribute(): string
    {
        $ext = strtolower($this->extension);

        switch ($ext) {
            case 'pdf':
                return 'text-red-600';
            case 'doc':
            case 'docx':
                return 'text-blue-600';
            case 'xls':
            case 'xlsx':
                return 'text-green-600';
            case 'ppt':
            case 'pptx':
                return 'text-orange-600';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return 'text-purple-600';
            default:
                return 'text-gray-600';
        }
    }

    /**
     * Delete file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($file) {
            if (Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
            }
        });
    }
}
