<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Соглашение об отмене договора {{ $contract->contract_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
        }
        .document-content {
            line-height: 1.6;
            font-family: 'Times New Roman', serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="no-print fixed top-4 right-4 space-x-2">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Печать
        </button>
        <button onclick="window.close()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Закрыть
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white shadow-lg min-h-screen">
        <div class="p-8 document-content">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-lg font-bold uppercase">
                    СОГЛАШЕНИЕ
                    <br>
                    об отмене договора {{ $contract->contract_number }} от {{ $contract->contract_date->format('d.m.Y') }} г.
                </h1>
            </div>

            <div class="text-right mb-8">
                <p>г. Ташкент &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $generated_date }} г.</p>
            </div>

            <!-- Document Body -->
            <div class="space-y-4 text-justify">
                <p>
                    "Ташкент Инвест компанияси" АО (далее - Компания) в лице Председателя Правления,
                    действующего на основании Устава, Б.А. Шакирова, с одной стороны, и
                    {{ $contract->subject->display_name }} (далее - Инвестор)
                    @if($contract->subject->is_legal_entity)
                        в лице директора, действующего на основании Устава,
                    @endif
                    с другой стороны, подписавшие договор "Об осуществлении платежа дополнительного сбора,
                    установленного за выдачу архитектурно-планировочного задания"
                    № {{ $contract->contract_number }} (далее - Договор),
                    заключили настоящее соглашение о нижеследующем:
                </p>

                <p>
                    <strong>1.</strong>
                    @if($cancellation->reason->type === 'council_rejection')
                        В связи с тем, что Инвестором по проектному зданию (сооружению) разработана
                        проектно-сметная документация в установленном порядке, и получено уведомление
                        о отклонении согласования Архитектурно-градостроительного совета города Ташкент
                        (копия приложена), Договор отменяется.
                    @elseif($cancellation->reason->type === 'company_request')
                        В связи с официальным письмом Компании об отмене Договора по причине
                        {{ strtolower($cancellation->reason->name_ru) }}, Договор отменяется.
                    @elseif($cancellation->reason->type === 'self_wish')
                        По собственному желанию Инвестора, учитывая что Инвестором по проектному зданию (сооружению)
                        разработана проектно-сметная документация в установленном порядке и установлено,
                        что частные дошкольные и общеобразовательные учреждения всех видов освобождены
                        от всех видов налогов (кроме социального налога) и сборов согласно подпункту "g"
                        пункта 5 Постановления Президента Республики Узбекистан от 26.07.2023 года № ПП-236,
                        что принимается к сведению, Договор отменяется.
                    @else
                        По согласованию сторон в связи с {{ strtolower($cancellation->reason->name_ru) }}, Договор отменяется.
                    @endif
                </p>

                <p>
                    <strong>2.</strong> Согласно пунктам 2.1, 2.2 и 2.3 Договора Инвестором
                    (на основании электронного платежного поручения от {{ $cancellation->cancellation_date->format('d.m.Y') }} г.
                    на сумму {{ number_format($cancellation->paid_amount) }} сум)
                    денежные средства в размере {{ round(($cancellation->paid_amount / $contract->total_amount) * 100, 2) }}%
                    от суммы договора, то есть {{ number_format($cancellation->paid_amount) }}
                    ({{ $this->numberToWords($cancellation->paid_amount) }}) сум,
                    поступили на расчетный счет Фонда в счет сбора, что принимается к сведению.
                </p>

                <p>
                    <strong>3.</strong> Компания принимает на себя обязательство в течение 5 (пяти) рабочих дней
                    после подписания настоящего соглашения возвратить предварительно оплаченные денежные средства
                    в размере {{ number_format($cancellation->refund_amount ?: $cancellation->paid_amount) }} сум,
                    включая все банковские расходы, на расчетный счет Инвестора, указанный в настоящем соглашении.
                </p>

                <p>
                    <strong>4.</strong> За исключением случая, предусмотренного в пункте 3 настоящего соглашения,
                    Стороны не имеют друг к другу никаких финансовых, имущественных и иных претензий.
                </p>

                <p>
                    <strong>5.</strong> С момента подписания настоящего соглашения все взаимные обязательства
                    Сторон по Договору утрачивают силу, за исключением случая, предусмотренного в пункте 3
                    настоящего соглашения.
                </p>

                <p>
                    <strong>6.</strong> Насто
                </p>


