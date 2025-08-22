<!-- resources/views/documents/demand_notice.blade.php -->
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Требование по договору {{ $contract->contract_number }}</title>
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
            <i data-feather="printer" class="w-4 h-4 mr-2 inline"></i>
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
                <div class="flex justify-between items-start mb-6">
                    <div class="text-left">
                        <p class="font-bold">"Ташкент Инвест компанияси" АО</p>
                        <p class="text-sm">Республика Узбекистан, г. Ташкент,</p>
                        <p class="text-sm">Чиланзарский район, ул. Ислама Каримова, 51</p>
                        <p class="text-sm">Почтовый индекс: 100066</p>
                        <p class="text-sm">Тел.: +998712100261</p>
                        <p class="text-sm">E-mail: info@tashkentinvest.com</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold">{{ $contract->subject->is_legal_entity ? 'Предприятию:' : 'Гражданину:' }}</p>
                        <p class="font-bold">{{ $contract->subject->display_name }}</p>
                        @if($contract->subject->is_legal_entity)
                            <p class="text-sm">(ИНН: {{ $contract->subject->inn }})</p>
                        @else
                            <p class="text-sm">(ПИНФЛ: {{ $contract->subject->pinfl }})</p>
                        @endif
                        <p class="text-sm mt-2">Адрес: {{ $contract->subject->legal_address ?: $contract->subject->physical_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Document Title -->
            <div class="text-center mb-8">
                <h1 class="text-xl font-bold uppercase">ТРЕБОВАНИЕ</h1>
                <p class="text-sm mt-2">(о нарушении сроков платежей)</p>
                <p class="text-sm">№____ от «___» _________ {{ date('Y') }} г.</p>
            </div>

            <!-- Document Body -->
            <div class="space-y-4 text-justify">
                <p>
                    Между "Ташкент Инвест компанияси" АО и {{ $contract->subject->is_legal_entity ? 'предприятием' : 'гражданином' }}:
                    {{ $contract->subject->display_name }} (далее - {{ $contract->subject->is_legal_entity ? 'Предприятие' : 'Гражданин' }})
                    {{ $contract->contract_date->format('d.m.Y') }} года заключен договор
                    "Об осуществлении платежа дополнительного сбора, установленного за выдачу архитектурно-планировочного задания"
                    № {{ $contract->contract_number }} (далее - Договор).
                </p>

                <p>
                    Согласно разделу 4 Договора по выполнению обязательств проведен мониторинг,
                    в результате которого установлено несоблюдение сроков платежа дополнительного сбора.
                </p>

                <p>В частности, согласно Договору:</p>

                <p class="ml-4">
                    пункту 2.2 второго абзаца, 20 (двадцать) процентов от суммы сбора должно быть оплачено
                    в течение 3 (трех) рабочих дней с даты заключения Договора;
                </p>

                <p class="ml-4">
                    пункту 2.2 третьего абзаца, оставшиеся 80 (восемьдесят) процентов суммы сбора
                    должны выплачиваться в установленные сроки без задержки согласно плану-графику,
                    приведенному в приложении к договору.
                </p>

                <p>
                    К {{ $generated_date }} г. согласно пункту 2.2 Договора {{ $contract->subject->is_legal_entity ? 'предприятием' : 'гражданином' }}
                    должна быть оплачена сумма сбора в размере
                    <strong>{{ number_format($total_debt - $total_penalty) }} ({{ $this->numberToWords($total_debt - $total_penalty) }})</strong> сум.
                    Однако на сегодняшний день указанный платеж не осуществлен.
                </p>

                <p>
                    В связи с неосуществлением платежей в установленные сроки, Компания согласно пункту 6.1 Договора
                    считает необходимым применить пеню в размере 0,01% от суммы сбора за каждый день просрочки
                    {{ $contract->subject->is_legal_entity ? 'предприятием' : 'гражданином' }},
                    но не более 15% от суммы просроченного платежа сбора.
                </p>

                <p>На этом основании рассчитаны основная задолженность и пени согласно следующей таблице:</p>

                <!-- Penalty Table -->
                <div class="my-6">
                    <table class="w-full border-collapse border border-gray-400 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-400 p-2 text-center">№</th>
                                <th class="border border-gray-400 p-2 text-center">Дата платежа по графику</th>
                                <th class="border border-gray-400 p-2 text-center">Сумма платежа по графику</th>
                                <th class="border border-gray-400 p-2 text-center">Неоплаченная сумма по графику</th>
                                <th class="border border-gray-400 p-2 text-center">Количество дней просрочки</th>
                                <th class="border border-gray-400 p-2 text-center">Рассчитанная сумма пени</th>
                                <th class="border border-gray-400 p-2 text-center">Общая сумма к доплате</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($penalties as $index => $penalty)
                            <tr>
                                <td class="border border-gray-400 p-2 text-center">{{ $index + 1 }}</td>
                                <td class="border border-gray-400 p-2 text-center">
                                    {{ \Carbon\Carbon::createFromDate($penalty['year'], $penalty['quarter'] * 3, 1)->endOfQuarter()->format('d.m.Y') }}
                                </td>
                                <td class="border border-gray-400 p-2 text-right">{{ number_format($penalty['scheduled_amount'], 2) }}</td>
                                <td class="border border-gray-400 p-2 text-right">{{ number_format($penalty['unpaid_amount'], 2) }}</td>
                                <td class="border border-gray-400 p-2 text-center">{{ $penalty['overdue_days'] }}</td>
                                <td class="border border-gray-400 p-2 text-right">{{ number_format($penalty['penalty_amount'], 2) }}</td>
                                <td class="border border-gray-400 p-2 text-right">{{ number_format($penalty['unpaid_amount'] + $penalty['penalty_amount'], 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="bg-gray-100 font-bold">
                                <td colspan="5" class="border border-gray-400 p-2 text-center">ИТОГО:</td>
                                <td class="border border-gray-400 p-2 text-right">{{ number_format($total_penalty, 2) }}</td>
                                <td class="border border-gray-400 p-2 text-right">{{ number_format($total_debt, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p>
                    Исходя из вышеизложенного, уведомляем Вас о необходимости осуществления платежей согласно договору
                    <strong>в течение {{ $deadline_days }} (трех) рабочих дней</strong> на {{ $generated_date }} г.
                    в размере сохраняющейся согласно графику платежей
                    <strong>{{ number_format($total_debt, 2) }} ({{ $this->numberToWords($total_debt) }})</strong> сум,
                    включающей <strong>основной долг</strong> и <strong>{{ number_format($total_penalty, 2) }} ({{ $this->numberToWords($total_penalty) }})</strong> сум <strong>пени</strong>,
                    в установленном порядке.
                </p>

                <p class="font-bold text-red-600">
                    В случае неосуществления платежей в установленный срок, Компания обратится в суд
                    с исковым заявлением о взыскании задолженности, а также в уполномоченные органы
                    о пересмотре заключения Архитектурно-градостроительного совета по согласованию
                    проектно-сметной документации и архитектурно-планировочного задания,
                    выданного для Вашего объекта - <strong>ПРЕДУПРЕЖДАЕМ</strong>.
                </p>

                <div class="mt-8 flex justify-between items-end">
                    <div>
                        <p>С уважением,</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold">"Ташкент Инвест компанияси" АО</p>
                        <p class="font-bold">Председатель Правления</p>
                        <div class="mt-8 border-b border-gray-400 w-32 mx-auto"></div>
                        <p class="mt-2 font-bold">Б. Шакиров</p>
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

<!-- resources/views/documents/amendment.blade.php -->
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

<!-- resources/views/documents/cancellation.blade.php -->
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
                    <strong>6.</strong> Настоящее соглашение составлено на узбекском языке в двух подлинных экземплярах,
                    каждый экземпляр имеет равную юридическую силу и каждой Стороне передается по одному экземпляру.
                </p>

                <p>
                    <strong>7.</strong> Настоящее дополнительное соглашение вступает в силу с даты подписания
                    и действует в течение срока действия Договора.
                </p>

                <!-- Signatures -->
                <div class="mt-12 grid grid-cols-2 gap-8">
                    <div>
                        <p class="font-bold">КОМПАНИЯ</p>
                        <p class="text-sm mt-2">
                            Адрес компании: г. Ташкент, Чиланзарский район,
                            МФЯ Бешёгоч, ул. Ислама Каримова, дом 51
                        </p>
                        <p class="text-sm">СТИР компании: 310731897</p>
                        <p class="text-sm">Телефон: (+998) 71 210 02 61</p>

                        <div class="mt-8">
                            <div class="border-b border-gray-400 w-48 mb-2"></div>
                            <p class="font-bold">Шакиров Бахром Аскаралиевич</p>
                        </div>
                    </div>

                    <div>
                        <p class="font-bold">ИНВЕСТОР</p>
                        <p class="text-sm mt-2">
                            Адрес проживания: {{ $contract->subject->physical_address ?: $contract->subject->legal_address }}
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
                        <p class="text-sm">Х/р: {{ $contract->subject->bank_account }}</p>
                        <p class="text-sm">МФО: {{ $contract->subject->bank_code }}</p>
                        <p class="text-sm">Банк: {{ $contract->subject->bank_name }}</p>
                        @endif

                        <div class="mt-8">
                            <div class="border-b border-gray-400 w-48 mb-2"></div>
                            <p class="font-bold">{{ $contract->subject->display_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Agreement Section -->
                <div class="mt-12 text-center">
                    <p class="font-bold">Согласовано:</p>
                    <div class="grid grid-cols-3 gap-8 mt-6">
                        <div>
                            <div class="border-b border-gray-400 w-24 mx-auto mb-2"></div>
                            <p class="text-sm">Кодиров Р.</p>
                        </div>
                        <div>
                            <div class="border-b border-gray-400 w-24 mx-auto mb-2"></div>
                            <p class="text-sm">Мирфозилов М.</p>
                        </div>
                        <div>
                            <div class="border-b border-gray-400 w-24 mx-auto mb-2"></div>
                            <p class="text-sm">Мирзаев Б.</p>
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
