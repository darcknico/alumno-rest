<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Functions\AuxiliarFunction;

class SqlAudit extends Model implements \OwenIt\Auditing\Contracts\Audit
{
    use \OwenIt\Auditing\Audit;

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'old_values'   => 'json',
        'new_values'   => 'json',
        'auditable_id' => 'integer',
    ];

    

    /**
    public function getOldValuesAttribute(){
        return AuxiliarFunction::rename_json($this['old_values']);
    }

    public function getNewValuesAttribute(){
        return AuxiliarFunction::rename_json($this['new_values']);
    }
     */

    /**
     * {@inheritdoc}
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        return $this->morphTo();
    }
}