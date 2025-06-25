<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');
        $contacts = [
            'phone' => $settings['contact_phone'] ?? '+380 (63) 755-42-70',
            'viber' => $settings['contact_viber'] ?? '+380 (63) 755-42-70',
            'email' => $settings['contact_email'] ?? 'bookseller.in.ua@gmail.com',
        ];
        $delivery = [
            'title' => $settings['delivery_title'] ?? 'Доставка в інтернет-магазині «BookSeller»',
            'description' => $settings['delivery_description'] ?? 'Швидка та надійна доставка книг по всій Україні через службу «Нова Пошта»',
            'np_branch_title' => $settings['delivery_np_branch_title'] ?? 'Нова Пошта (відділення або поштомат)',
            'np_branch_desc' => $settings['delivery_np_branch_desc'] ?? 'Отримайте замовлення у найближчому відділенні або поштоматі Нової Пошти',
            'np_branch_price' => $settings['delivery_np_branch_price'] ?? 'згідно з тарифами компанії Нова Пошта',
            'np_address_title' => $settings['delivery_np_address_title'] ?? 'Нова Пошта (адресна доставка)',
            'np_address_desc' => $settings['delivery_np_address_desc'] ?? 'Доставка безпосередньо за вказаною адресою у зручний для вас час',
            'np_address_price' => $settings['delivery_np_address_price'] ?? 'згідно з тарифами компанії Нова Пошта',
            'howto_title' => $settings['delivery_howto_title'] ?? 'Як оформити замовлення:',
            'howto_list' => $settings['delivery_howto_list'] ?? "Оберіть потрібні книги та додайте їх до кошика\nПерейдіть до оформлення замовлення\nВкажіть спосіб доставки та адресу\nОберіть зручний спосіб оплати",
            'terms_title' => $settings['delivery_terms_title'] ?? '⏰ Терміни доставки:',
            'terms_list' => $settings['delivery_terms_list'] ?? "По Україні: 1-3 робочих дні\nКиїв: 1-2 робочих дні\nВіддалені регіони: 2-4 робочих дні",
        ];
        $payment = [
            'title' => $settings['payment_title'] ?? 'Оплата в інтернет-магазині «BookSeller»',
            'np_title' => $settings['payment_np_title'] ?? 'Нова Пошта (відділення або поштомат)',
            'np_card' => $settings['payment_np_card'] ?? 'Оплата платіжною карткою Visa / Mastercard (Без комісії)',
            'np_link' => $settings['payment_np_link'] ?? 'Надсилаємо посилання для оплати, після чого Ви отримуєте чек',
            'np_bank' => $settings['payment_np_bank'] ?? 'Безготівковий переказ (за IBAN) згідно рахунку.',
            'np_bank_note' => $settings['payment_np_bank_note'] ?? 'Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.',
            'np_cod' => $settings['payment_np_cod'] ?? 'Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми',
            'np_cod_note' => $settings['payment_np_cod_note'] ?? 'Зверніть увагу, що доставка при накладеному платежі є платною!',
            'np_cod_important' => $settings['payment_np_cod_important'] ?? 'Для оплати при отриманні потрібно вести 20% від суми',
            'np_cod_important2' => $settings['payment_np_cod_important2'] ?? 'Для оплати при отриманні потрібно вести 20% від суми',
            'np_address_title' => $settings['payment_np_address_title'] ?? 'Нова Пошта (адресна доставка)',
            'np_address_card' => $settings['payment_np_address_card'] ?? 'Оплата платіжною карткою Visa / Mastercard (Без комісії)',
            'np_address_link' => $settings['payment_np_address_link'] ?? 'Надсилаємо посилання для оплати, після чого Ви отримуєте чек',
            'np_address_bank' => $settings['payment_np_address_bank'] ?? 'Безготівковий переказ (за IBAN) згідно рахунку.',
            'np_address_bank_note' => $settings['payment_np_address_bank_note'] ?? 'Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.',
            'np_address_cod' => $settings['payment_np_address_cod'] ?? 'Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми',
            'np_address_cod_note' => $settings['payment_np_address_cod_note'] ?? 'Зверніть увагу, що доставка при накладеному платежі є платною!',
            'np_address_cod_important' => $settings['payment_np_address_cod_important'] ?? 'Для оплати при отриманні потрібно вести 20% від суми',
        ];
        return response()->json(array_merge($settings->toArray(), ['contacts' => $contacts, 'delivery' => $delivery, 'payment' => $payment]));
    }

    public function getContacts()
    {
        $settings = Setting::pluck('value', 'key');
        return response()->json([
            'phone' => $settings['contact_phone'] ?? '+380 (63) 755-42-70',
            'viber' => $settings['contact_viber'] ?? '+380 (63) 755-42-70',
            'email' => $settings['contact_email'] ?? 'bookseller.in.ua@gmail.com',
        ]);
    }

    public function getDeliveryCost()
    {
        $cost = $this->getDeliveryCostValue();
        return response()->json([
            'delivery_cost' => $cost
        ]);
    }

    // Метод для внутреннего использования
    public function getDeliveryCostValue()
    {
        $cost = Setting::getValue('delivery_cost', 250);
        Log::info('Delivery cost from settings: ' . $cost);
        return (float) $cost;
    }

    /**
     * Получить график работы
     */
    public function getWorkingHours()
    {
        $workingHours = $this->getWorkingHoursArray();

        return response()->json([
            'working_hours' => $workingHours
        ]);
    }

    /**
     * Получить график работы в виде массива для внутреннего использования
     */
    public function getWorkingHoursArray(): array
    {
        $workingHours = [];

        // Будние дни
        if (Setting::getValue('working_hours.weekdays.enabled', '1') === '1') {
            $workingHours[] = [
                'type' => 'weekdays',
                'label' => Setting::getValue('working_hours.weekdays.label', 'ПН, ВТ, СР, ЧТ, ПТ'),
                'hours' => Setting::getValue('working_hours.weekdays.hours', 'з 9:00 до 18:00'),
                'enabled' => true,
            ];
        }

        // Суббота
        if (Setting::getValue('working_hours.saturday.enabled', '1') === '1') {
            $workingHours[] = [
                'type' => 'saturday',
                'label' => Setting::getValue('working_hours.saturday.label', 'Субота'),
                'hours' => Setting::getValue('working_hours.saturday.hours', 'з 10:00 до 15:00'),
                'enabled' => true,
            ];
        }

        // Воскресенье
        if (Setting::getValue('working_hours.sunday.enabled', '0') === '1') {
            $workingHours[] = [
                'type' => 'sunday',
                'label' => Setting::getValue('working_hours.sunday.label', 'Неділя'),
                'hours' => Setting::getValue('working_hours.sunday.hours', 'Вихідний'),
                'enabled' => true,
            ];
        }

        return $workingHours;
    }

    /**
     * Получить все настройки, связанные с графиком работы
     */
    public function getWorkingHoursSettings()
    {
        $settings = Setting::where('key', 'like', 'working_hours.%')->get();

        $groupedSettings = [
            'weekdays' => [
                'label' => Setting::getValue('working_hours.weekdays.label', 'ПН, ВТ, СР, ЧТ, ПТ'),
                'hours' => Setting::getValue('working_hours.weekdays.hours', 'з 9:00 до 18:00'),
                'enabled' => Setting::getValue('working_hours.weekdays.enabled', '1') === '1',
            ],
            'saturday' => [
                'label' => Setting::getValue('working_hours.saturday.label', 'Субота'),
                'hours' => Setting::getValue('working_hours.saturday.hours', 'з 10:00 до 15:00'),
                'enabled' => Setting::getValue('working_hours.saturday.enabled', '1') === '1',
            ],
            'sunday' => [
                'label' => Setting::getValue('working_hours.sunday.label', 'Неділя'),
                'hours' => Setting::getValue('working_hours.sunday.hours', 'Вихідний'),
                'enabled' => Setting::getValue('working_hours.sunday.enabled', '0') === '1',
            ],
        ];

        return response()->json([
            'working_hours_settings' => $groupedSettings
        ]);
    }

    /**
     * Обновить настройки графика работы
     */
    public function updateWorkingHours(Request $request)
    {
        $request->validate([
            'weekdays.label' => 'required|string|max:255',
            'weekdays.hours' => 'required|string|max:255',
            'weekdays.enabled' => 'boolean',
            'saturday.label' => 'required|string|max:255',
            'saturday.hours' => 'required|string|max:255',
            'saturday.enabled' => 'boolean',
            'sunday.label' => 'required|string|max:255',
            'sunday.hours' => 'required|string|max:255',
            'sunday.enabled' => 'boolean',
        ]);

        $data = $request->all();

        // Обновляем настройки будних дней
        Setting::setValue('working_hours.weekdays.label', $data['weekdays']['label']);
        Setting::setValue('working_hours.weekdays.hours', $data['weekdays']['hours']);
        Setting::setValue('working_hours.weekdays.enabled', $data['weekdays']['enabled'] ? '1' : '0');

        // Обновляем настройки субботы
        Setting::setValue('working_hours.saturday.label', $data['saturday']['label']);
        Setting::setValue('working_hours.saturday.hours', $data['saturday']['hours']);
        Setting::setValue('working_hours.saturday.enabled', $data['saturday']['enabled'] ? '1' : '0');

        // Обновляем настройки воскресенья
        Setting::setValue('working_hours.sunday.label', $data['sunday']['label']);
        Setting::setValue('working_hours.sunday.hours', $data['sunday']['hours']);
        Setting::setValue('working_hours.sunday.enabled', $data['sunday']['enabled'] ? '1' : '0');

        Log::info('Working hours settings updated', $data);

        return response()->json([
            'message' => 'Working hours updated successfully',
            'working_hours' => $this->getWorkingHoursArray()
        ]);
    }

    /**
     * Получить конкретную настройку
     */
    public function getSetting(string $key)
    {
        $value = Setting::getValue($key);

        if ($value === null) {
            return response()->json([
                'error' => 'Setting not found'
            ], 404);
        }

        return response()->json([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Обновить конкретную настройку
     */
    public function updateSetting(Request $request, string $key)
    {
        $request->validate([
            'value' => 'required|string'
        ]);

        Setting::setValue($key, $request->value);

        Log::info("Setting updated: {$key} = {$request->value}");

        return response()->json([
            'message' => 'Setting updated successfully',
            'key' => $key,
            'value' => $request->value
        ]);
    }

    public function getDeliveryTexts()
    {
        $settings = Setting::pluck('value', 'key');
        return response()->json([
            'title' => $settings['delivery_title'] ?? 'Доставка в інтернет-магазині «BookSeller»',
            'description' => $settings['delivery_description'] ?? 'Швидка та надійна доставка книг по всій Україні через службу «Нова Пошта»',
            'np_branch_title' => $settings['delivery_np_branch_title'] ?? 'Нова Пошта (відділення або поштомат)',
            'np_branch_desc' => $settings['delivery_np_branch_desc'] ?? 'Отримайте замовлення у найближчому відділенні або поштоматі Нової Пошти',
            'np_branch_price' => $settings['delivery_np_branch_price'] ?? 'згідно з тарифами компанії Нова Пошта',
            'np_address_title' => $settings['delivery_np_address_title'] ?? 'Нова Пошта (адресна доставка)',
            'np_address_desc' => $settings['delivery_np_address_desc'] ?? 'Доставка безпосередньо за вказаною адресою у зручний для вас час',
            'np_address_price' => $settings['delivery_np_address_price'] ?? 'згідно з тарифами компанії Нова Пошта',
            'howto_title' => $settings['delivery_howto_title'] ?? 'Як оформити замовлення:',
            'howto_list' => $settings['delivery_howto_list'] ?? "Оберіть потрібні книги та додайте їх до кошика\nПерейдіть до оформлення замовлення\nВкажіть спосіб доставки та адресу\nОберіть зручний спосіб оплати",
            'terms_title' => $settings['delivery_terms_title'] ?? '⏰ Терміни доставки:',
            'terms_list' => $settings['delivery_terms_list'] ?? "По Україні: 1-3 робочих дні\nКиїв: 1-2 робочих дні\nВіддалені регіони: 2-4 робочих дні",
        ]);
    }

    public function getPaymentTexts()
    {
        $settings = Setting::pluck('value', 'key');
        return response()->json([
            'title' => $settings['payment_title'] ?? 'Оплата в інтернет-магазині «BookSeller»',
            'np_title' => $settings['payment_np_title'] ?? 'Нова Пошта (відділення або поштомат)',
            'np_card' => $settings['payment_np_card'] ?? 'Оплата платіжною карткою Visa / Mastercard (Без комісії)',
            'np_link' => $settings['payment_np_link'] ?? 'Надсилаємо посилання для оплати, після чого Ви отримуєте чек',
            'np_bank' => $settings['payment_np_bank'] ?? 'Безготівковий переказ (за IBAN) згідно рахунку.',
            'np_bank_note' => $settings['payment_np_bank_note'] ?? 'Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.',
            'np_cod' => $settings['payment_np_cod'] ?? 'Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми',
            'np_cod_note' => $settings['payment_np_cod_note'] ?? 'Зверніть увагу, що доставка при накладеному платежі є платною!',
            'np_cod_important' => $settings['payment_np_cod_important'] ?? 'Для оплати при отриманні потрібно вести 20% від суми',
            'np_cod_important2' => $settings['payment_np_cod_important2'] ?? 'Для оплати при отриманні потрібно вести 20% від суми',
            'np_address_title' => $settings['payment_np_address_title'] ?? 'Нова Пошта (адресна доставка)',
            'np_address_card' => $settings['payment_np_address_card'] ?? 'Оплата платіжною карткою Visa / Mastercard (Без комісії)',
            'np_address_link' => $settings['payment_np_address_link'] ?? 'Надсилаємо посилання для оплати, після чого Ви отримуєте чек',
            'np_address_bank' => $settings['payment_np_address_bank'] ?? 'Безготівковий переказ (за IBAN) згідно рахунку.',
            'np_address_bank_note' => $settings['payment_np_address_bank_note'] ?? 'Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.',
            'np_address_cod' => $settings['payment_np_address_cod'] ?? 'Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми',
            'np_address_cod_note' => $settings['payment_np_address_cod_note'] ?? 'Зверніть увагу, що доставка при накладеному платежі є платною!',
            'np_address_cod_important' => $settings['payment_np_address_cod_important'] ?? 'Для оплати при отриманні потрібно вести 20% від суми',
        ]);
    }
}
