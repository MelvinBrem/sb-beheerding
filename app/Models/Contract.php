<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Request;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = ['hs_object_id', 'hs_contract_name', 'hs_contract_url', 'hs_pipeline_stage'];

    protected function hsPipelineStage(): Attribute
    {
        $stageNames = [
            95324283 => 'NEW CONTRACTS',
            95503437 => 'CONTRACT SETUP',
            95324284 => 'CONTRACTS',
            95209102 => 'STOPPED CONTRACTS'
        ];

        return Attribute::make(
            get: fn ($value) => $stageNames[$value],
        );
    }

    protected function hsContractName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => preg_replace("/^([\d\.]+ \| )?(Maintenance(\s\(WP\))? \| )?/", '', $value),
        );
    }
}
