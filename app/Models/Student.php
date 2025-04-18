<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'roll_number',
        'batch',
        'admission_date'
    ];

    protected $casts = [
        'admission_date' => 'date'
    ];

    /**
     * Get the user that owns the student profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the test attempts for the student.
     */
    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class, 'user_id', 'user_id');
    }
} 