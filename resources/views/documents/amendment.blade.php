<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дополнительное соглашение к договору {{ $contract->contract_number }}</title>
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
                <p class="text-sm">
                    Дополнительное соглашение №{{ $amendment->amendment_number }} к договору
                    {{ $contract->contract_number }} от {{ $contract->contract_date->format('d.m.Y') }} г.
                    <br>
                    "Об осуществлении платежа дополнительного сбора, установленного
                    за выдачу архитектурно-планировочного задания"
                </p>
            </div>

            <div class="text-right mb-8">
                <p>г. Ташкент &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $generated_date }} г.</p>
            </div>

            <!-- Document Body -->
            <div class="space-y-4 text-justify">
                <p>
                    "Ташкент Инвест компанияси" АО (далее - Компания) в лице Председателя Правления
                    Шакирова Бахрома Аскаралиевича, действующего на основании Устава, с одной стороны, и
                </p>

                <p>
                    {{ $contract->subject->display_name }} (далее - Инвестор)
                    @if($contract->subject->is_legal_entity)
                        в лице директора, действующего на основании Устава,
                    @endif
                    с другой стороны, вместе именуемые Сторонами, а по отдельности Стороной,
                    заключили настоящее дополнительное соглашение в соответствии с
                    Постановлением Президента Республики Узбекистан от 26 июля 2023 года № ПП-236
                    "О мерах по проведению правового эксперимента по реализации инвестиционных проектов
                    и улучшению городской инфраструктуры на основе взаимовыгодного сотрудничества
                    между государственными и предпринимательскими субъектами в городе Ташкент" о нижеследующем:
                </p>

                <p>
                    <strong>1.</strong> Пункт 2.1 Договора "Об осуществлении платежа дополнительного сбора, установленного
                    за выдачу архитектурно-планировочного задания" {{ $contract->contract_number }}
                    от {{ $contract->contract_date->format('d.m.Y') }} г. (далее - Договор) изложить в следующей редакции:
                </p>

                <p class="ml-8">
                    "2.1. Размер сбора в соответствии с Постановлением Президента Республики Узбекистан
                    от 26 июля 2023 года № ПП-236 "О мерах по проведению правового эксперимента по реализации
                    инвестиционных проектов и улучшению городской инфраструктуры на основе взаимовыгодного
                    сотрудничества между государственными и предпринимательскими субъектами в городе Ташкент"
                    и Положением, утвержденным постановлением Кабинета Министров от 25 марта 2024 года № 149
                    "О порядке включения части расходов на создание инженерно-коммуникационных сетей
                    и транспортной инфраструктуры в городе Ташкент в стоимость платы за разработку
                    архитектурно-планировочного задания для проектирования строительства или реконструкции
                    объекта градостроительной деятельности", в размере 1 базовой расчетной величины
                    за каждый кубический метр общего строительного объема, на основании "Механизма расчета
                    размера платы за часть расходов на создание инженерно-коммуникационных сетей
                    и транспортной инфраструктуры в городе Ташкент", утвержденного решением
                    Ташкентского городского Кенгаша народных депутатов от 2 июля 2024 года № VI-104-94-14-0-K/24,
                    рассчитанного с поверхности проектного здания (сооружения), для которого выдано АПЗ,
                    и составляет {{ number_format($amendment->new_volume, 1) }} ({{ $this->numberToWords($amendment->new_volume) }})
                    кубических метров объема при применении коэффициента 0,50 -
                    {{ number_format($amendment->new_amount) }} ({{ $this->numberToWords($amendment->new_amount) }}) сум.";
                </p>

                <p>
                    <strong>2.</strong> Настоящее дополнительное соглашение составлено на государственном языке
                    (узбекском языке) в двух подлинных экземплярах, имеющих одинаковую юридическую силу,
                    причем один из подлинных экземпляров хранится у Инвестора,
                    а второй - у Компании в установленном порядке.
                </p>

                <p>
                    <strong>3.</strong> Настоящее дополнительное соглашение является неотъемлемой частью Договора.
                </p>

                <p>
                    <strong>4.</strong> Пункты Договора, изменение которых не предусмотрено в настоящем соглашении,
                    остаются в силе.
                </p>

                <p>
                    <strong>5.</strong> Настоящее дополнительное соглашение вступает в силу с даты подписания
                    и действует в течение срока действия Договора.
                </p>

                <!-- Signatures -->
                <div class="mt-12 grid grid-cols-2 gap-8">
                    <div>
                        <p class="font-bold">КОМПАНИЯ</p>
                        <p class="text-sm mt-2">(Банковские реквизиты Фонда)</p>
                        <p class="text-sm">Расчетный счет: 2020 4000 7001 0116 3002</p>
                        <p class="text-sm">СТИР Фонда: 201623064</p>
                        <p class="text-sm">Банковский код: (МФО) 00440</p>
                        <p class="text-sm">
                            Название банка: АКБ "Узсаноаткурилишбанк";
                            Обслуживающее подразделение банка: г. Ташкент,
                            Юнусабадский район, ул. Шахрисабз, дом 3.
                            Корпоративный центр АКБ "Узсаноаткурилишбанк".
                        </p>

                        <div class="mt-8">
                            <div class="border-b border-gray-400 w-48 mb-2"></div>
                            <p class="font-bold">Шакиров Бахром Аскаралиевич</p>
                        </div>
                    </div>

                    <div>
                        <p class="font-bold">ИНВЕСТОР</p>
                        <p class="text-sm mt-2">
                            Адрес: {{ $contract->subject->legal_address ?: $contract->subject->physical_address }}
                        </p>
                        @if($contract->subject->is_legal_entity)
                        <p class="text-sm">СТИР: {{ $contract->subject->inn }}</p>
                        <p class="text-sm">ОКЭД: {{ $contract->subject->oked }}</p>
                        @else
                        <p class="text-sm">СТИР/ЖШШИР: {{ $contract->subject->pinfl }}</p>
                        @endif
                        @if($contract->subject->phone)
                        <p class="text-sm">Телефон: {{ $contract->subject->phone }}</p>
                        @endif
                        @if($contract->subject->bank_account)
                        <p class="text-sm">Р/с: {{ $contract->subject->bank_account }}</p>
                        <p class="text-sm">МФО: {{ $contract->subject->bank_code }}</p>
                        <p class="text-sm">Банк: {{ $contract->subject->bank_name }}</p>
                        @endif

                        <div class="mt-8">
                            <div class="border-b border-gray-400 w-48 mb-2"></div>
                            <p class="font-bold">{{ $contract->subject->display_name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        feather.replace();
    </script>
</body>
</html>
