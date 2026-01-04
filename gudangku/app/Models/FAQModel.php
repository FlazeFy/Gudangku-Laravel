<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Faq",
 *     type="object",
 *     required={"id", "faq_question", "is_show", "created_at"},
 *
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary key for the FAQ"),
 *     @OA\Property(property="faq_question", type="string", maxLength=144, description="FAQ question text"),
 *     @OA\Property(property="faq_answer", type="string", maxLength=255, nullable=true, description="Answer for the FAQ question"),
 *     @OA\Property(property="is_show", type="boolean", description="Indicates whether the FAQ is visible to users"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the FAQ was created")
 * )
 */

class FAQModel extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $incrementing = false;

    protected $table = 'faq';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'faq_question', 'faq_answer', 'created_at', 'is_show'];
}
