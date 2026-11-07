<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Models\Building;
use Illuminate\Validation\Rule;

class UpdateBuildingRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('building')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:2',
                'max:32',
                'required',
                Rule::unique('buildings')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('building')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $siteId = $this->input('site_id');

            if (empty($siteId)) {
                return;
            }

            $parentId = $this->input('building_id');
            if (! empty($parentId)) {
                $parentSiteId = Building::whereKey($parentId)->value('site_id');
                if ((string) $parentSiteId !== (string) $siteId) {
                    $validator->errors()->add('building_id', trans('cruds.building.errors.parent_different_site'));
                }
            }

            $childrenIds = array_filter((array) $this->input('buildings', []));
            if (! empty($childrenIds)) {
                $mismatch = Building::whereIn('id', $childrenIds)
                    ->where(function ($q) use ($siteId) {
                        $q->whereNull('site_id')->orWhere('site_id', '!=', $siteId);
                    })
                    ->exists();
                if ($mismatch) {
                    $validator->errors()->add('buildings', trans('cruds.building.errors.children_different_site'));
                }
            }
        });
    }
}
