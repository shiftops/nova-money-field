<?php

namespace Vyuldashev\NovaMoneyField;

use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;
use Money\Currencies\AggregateCurrencies;
use Money\Currencies\BitcoinCurrencies;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

class Money extends Number
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-money-field';

    public $inMinorUnits;

    public $inline;

    public function __construct($name, $currency = 'USD', $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->withMeta([
            'currency' => $currency,
            'subUnits' => $this->subunits($currency),
            'inline' => $this->inline
        ]);

        $this->step(1 / $this->minorUnit($currency));

        $this
            ->resolveUsing(function ($value) use ($currency, $resolveCallback) {
                if ($resolveCallback !== null) {
                    $value = call_user_func_array($resolveCallback, func_get_args());
                }

                return $this->inMinorUnits ? $value / $this->minorUnit($currency) : (float) $value;
            })
            ->fillUsing(function (NovaRequest $request, $model, $attribute, $requestAttribute) use ($currency) {
                $value = $request[$requestAttribute];

                if ($this->inMinorUnits) {
                    $value *= $this->minorUnit($currency);
                }

                $model->{$attribute} = $value;
            });
    }

    protected function resolveAttribute($resource, $attribute)
    {
        $this->setResourceId(data_get($resource, $resource->getKeyName()));

        return parent::resolveAttribute($resource, $attribute);
    }

    protected function setResourceId($id)
    {
        return $this->withMeta([
            'id' => $id,
            'nova_path' => Config::get('nova.path'),
            'in_minor_units' => $this->inMinorUnits
        ]);
    }

    /**
     * The value in database is store in minor units (cents for dollars).
     */
    public function storedInMinorUnits()
    {
        $this->inMinorUnits = true;

        return $this;
    }

    /**
     * Enable inline editing (on index field)
     */
    public function inline($updatedMessage = null)
    {
        $this->withMeta(['inline' => true, 'message' => $updatedMessage]);

        return $this;
    }

    public function locale($locale)
    {
        return $this->withMeta(['locale' => $locale]);
    }

    public function subUnits(string $currency)
    {
        return (new AggregateCurrencies([
            new ISOCurrencies(),
            new BitcoinCurrencies(),
        ]))->subunitFor(new Currency($currency));
    }

    public function minorUnit($currency)
    {
        return 10 ** $this->subUnits($currency);
    }
}
