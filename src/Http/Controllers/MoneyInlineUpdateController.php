<?php

namespace Vyuldashev\NovaMoneyField\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;

class MoneyInlineUpdateController extends Controller
{
    public function __invoke(NovaRequest $request)
    {
        $resourceClass = $request->resource();
        $resourceValidationRules = $resourceClass::rulesForUpdate($request);
        $fieldValidationRules = $resourceValidationRules[$request->attribute];

        if (!empty($fieldValidationRules)) {
            $validatedData = $request->validate([
                'value' => $fieldValidationRules,
            ]);
        }

        $model = $request->model()->find($request->id);
        $model->{$request->attribute} = ($request->in_minor_units ?? false) ? $request->value * 100 : $request->value;
        $model->save();
    }
}
