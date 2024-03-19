<?php

namespace App\Transformers;

use App\Models\Tax;
use App\Models\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

/**
 * Class TaxTransformer
 *
 * @package App\Http\Transformers\Tax
 */
class UserTransformer extends TransformerAbstract
{
    /**
     * Transform data
     *
     * @param  User  $row
     *
     * @return array
     */
    public function transform(User $row): array
    {
        return [
            'id'          => (int) $row->id,
            'name'        => $row->name,
            'email'       => $row->email,
        ];
    }
}
