<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'prediction_id',
        'category',
        'feedback_type',
        'subject',
        'message',
        'contest_reason',
        'screenshot_url',
        'status',
        'admin_response',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Catégories de feedback
     */
    const CATEGORY_PREDICTION = 'prediction';
    const CATEGORY_PAYMENT = 'payment';
    const CATEGORY_BUG = 'bug';
    const CATEGORY_SUGGESTION = 'suggestion';
    const CATEGORY_OTHER = 'other';

    /**
     * Types de feedback pour les prédictions
     */
    const TYPE_HELPFUL = 'helpful';
    const TYPE_NOT_HELPFUL = 'not_helpful';

    /**
     * Statuts
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    /**
     * Relation: Le feedback appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation: Le feedback peut être lié à une prédiction
     */
    public function prediction(): BelongsTo
    {
        return $this->belongsTo(Prediction::class);
    }

    /**
     * Relation: Admin qui a résolu le feedback
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope: Feedbacks en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Feedbacks résolus
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope: Par catégorie
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Par type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('feedback_type', $type);
    }

    /**
     * Scope: Feedbacks positifs (helpful)
     */
    public function scopePositive($query)
    {
        return $query->where('feedback_type', self::TYPE_HELPFUL);
    }

    /**
     * Scope: Feedbacks négatifs (not_helpful)
     */
    public function scopeNegative($query)
    {
        return $query->where('feedback_type', self::TYPE_NOT_HELPFUL);
    }

    /**
     * Scope: Par utilisateur
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Par prédiction
     */
    public function scopeByPrediction($query, int $predictionId)
    {
        return $query->where('prediction_id', $predictionId);
    }

    /**
     * Marquer comme en cours de traitement
     */
    public function markAsInProgress(): void
    {
        $this->update(['status' => self::STATUS_IN_PROGRESS]);
    }

    /**
     * Marquer comme résolu
     */
    public function markAsResolved(?int $adminId = null, ?string $response = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolved_by' => $adminId,
            'admin_response' => $response,
        ]);
    }

    /**
     * Fermer le feedback
     */
    public function close(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }

    /**
     * Vérifier si le feedback est en attente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifier si le feedback est résolu
     */
    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Créer un feedback rapide sur une prédiction
     */
    public static function createPredictionFeedback(
        int $userId, 
        int $predictionId, 
        string $type
    ): self {
        return self::create([
            'user_id' => $userId,
            'prediction_id' => $predictionId,
            'category' => self::CATEGORY_PREDICTION,
            'feedback_type' => $type,
            'status' => self::STATUS_RESOLVED, // Auto-résolu car c'est juste un vote
        ]);
    }

    /**
     * Obtenir les statistiques de feedback pour une prédiction
     */
    public static function getPredictionStats(int $predictionId): array
    {
        $feedbacks = self::where('prediction_id', $predictionId)->get();

        return [
            'total' => $feedbacks->count(),
            'helpful' => $feedbacks->where('feedback_type', self::TYPE_HELPFUL)->count(),
            'not_helpful' => $feedbacks->where('feedback_type', self::TYPE_NOT_HELPFUL)->count(),
        ];
    }
}

