<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class AvatarOperation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'avatars_operations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_hash', 'method', 'image_file', 'code'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    const METHOD_DELETE = 0;
    const METHOD_POST = 1;

    /**
     * The methods allowed
     *
     * @var array
     */
    public static $methods = [
        self::METHOD_DELETE => 'DELETE',
        self::METHOD_POST => 'POST',
    ];

    /**
     * Define an inverse one-to-one or many relationship.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function avatar()
    {
        return $this->belongsTo(Avatar::class, 'email_hash');
    }

    /**
     * Generate code
     * @return string
     */
    public static function generateCode()
    {
        return md5(uniqid(rand(), true));
    }
}