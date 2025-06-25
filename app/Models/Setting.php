<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    public static function getValue(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
    public static function getWorkingHours(): array
    {
        $workingHours = [];

        // Будние дни
        if (static::getValue('working_hours.weekdays.enabled', '1') === '1') {
            $workingHours[] = [
                'label' => static::getValue('working_hours.weekdays.label', 'ПН, ВТ, СР, ЧТ, ПТ'),
                'hours' => static::getValue('working_hours.weekdays.hours', 'з 9:00 до 18:00'),
            ];
        }

        // Суббота
        if (static::getValue('working_hours.saturday.enabled', '1') === '1') {
            $workingHours[] = [
                'label' => static::getValue('working_hours.saturday.label', 'Субота'),
                'hours' => static::getValue('working_hours.saturday.hours', 'з 10:00 до 15:00'),
            ];
        }

        // Воскресенье
        if (static::getValue('working_hours.sunday.enabled', '0') === '1') {
            $workingHours[] = [
                'label' => static::getValue('working_hours.sunday.label', 'Неділя'),
                'hours' => static::getValue('working_hours.sunday.hours', 'Вихідний'),
            ];
        }

        return $workingHours;
    }
}
