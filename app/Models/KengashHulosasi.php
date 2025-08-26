<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KengashHulosasi extends Model
{
    use HasFactory;

    protected $table = 'kengash_hulosasi';

    protected $fillable = [
        'kengash_hulosa_raqami',
        'kengash_hulosa_sanasi',
        'apz_raqami',
        'apz_berilgan_sanasi',
        'buyurtmachi',
        'buyurtmachi_stir_pinfl',
        'buyurtmachi_telefon',
        'bino_turi',
        'muammo_turi',
        'loyihachi',
        'loyihachi_stir_pinfl',
        'loyihachi_telefon',
        'loyiha_smeta_nomi',
        'tuman',
        'manzil',
        'status',
        'ozod_sababi',
        'qurilish_turi',
        'shartnoma_raqami',
        'shartnoma_sanasi',
        'shartnoma_qiymati',
        'fakt_tulov',
        'qarzdarlik',
        'tic_apz_id',
        'creator_user_id',
        'updater_user_id'
    ];

    protected $casts = [
        'kengash_hulosa_sanasi' => 'date',
        'apz_berilgan_sanasi' => 'date',
        'shartnoma_sanasi' => 'date',
        'shartnoma_qiymati' => 'decimal:2',
        'fakt_tulov' => 'decimal:2',
        'qarzdarlik' => 'decimal:2',
    ];

    /**
     * Get the files for the kengash hulosasi.
     */
    public function files(): HasMany
    {
        return $this->hasMany(KengashHulosiFile::class, 'kengash_hulosasi_id');
    }

    /**
     * Get the creator user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Get the updater user.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updater_user_id');
    }

    /**
     * Check if payment is exempted
     */
    public function isTulovdanOzod(): bool
    {
        return $this->status === 'Тўловдан озод этилган';
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return $this->status === 'Тўловдан озод этилган'
            ? 'Тўловдан озод'
            : 'Мажбурий тўлов';
    }

    /**
     * Get exemption reasons
     */
    public static function getExemptionReasons(): array
    {
        return [
            'davlat_byudjet' => 'Давлат бюджетидан молиялаштириладиган объектлар',
            'yakka_tartib_uy' => 'Якка тартибдаги уй-жойлар',
            'nodavlat_maktab' => 'Нодавлат мактабгача ва умумий ўрта таълим ташкилотлари',
            'ekspertiza_talab_emas' => 'Лойиҳа экспертизаси талаб этилмайдиган объектлар',
            'rekonst_hajm_ozgarmagan' => 'Қурилиш ҳажмини ўзгартирмасдан реконструкция қилинган',
            'renovatsiya_kompensatsiya' => 'Реновация ҳудудида мулкдорларнинг бузилган объектлари учун компенсация',
            'davlat_50foiz' => 'Қурилиш ҳажмининг 50% ва ундан ортиқ қисми давлатга беғараз топширилади',
            'ozini_boshqarish' => 'Фуқароларни ўз-ўзини бошқариш ташкилотлари жойлаштириладиган объектлар',
            'muhandislik_kom' => 'Муҳандислик-коммуникация тармоқлари ва транспорт инфратузилмаси',
            'sanoat_ishlab_chiqarish' => 'Саноат мақсадларидаги ишлаб чиқариш объектлари',
            'diniy_maqsad' => 'Диний мақсадларда фойдаланиладиган объектлар'
        ];
    }

    /**
     * Scope for exempted records
     */
    public function scopeTulovdanOzod($query)
    {
        return $query->where('status', 'Тўловдан озод этилган');
    }

    /**
     * Scope for mandatory payment records
     */
    public function scopeMajburiyTulov($query)
    {
        return $query->where('status', 'Мажбурий тўлов');
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('kengash_hulosa_raqami', 'like', "%{$search}%")
              ->orWhere('apz_raqami', 'like', "%{$search}%")
              ->orWhere('buyurtmachi', 'like', "%{$search}%")
              ->orWhere('loyihachi', 'like', "%{$search}%")
              ->orWhere('tuman', 'like', "%{$search}%");
        });
    }
}
