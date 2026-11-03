<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सेवा सम्झौता - {{ $agreement->school_name }} - {{ $agreement->agreement_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700;800&family=Kalimati&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Mukta', 'Kalimati', 'Noto Sans Devanagari', -apple-system, sans-serif;
            font-size: 13.5px;
            line-height: 1.55;
            color: #111827;
            background: #f3f4f6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Toolbar */
        .no-print-toolbar {
            background: #1e293b;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .no-print-toolbar .btn {
            background: #059669;
            color: white;
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .no-print-toolbar .btn:hover {
            background: #047857;
        }

        .no-print-toolbar .btn-secondary {
            background: #475569;
        }

        .no-print-toolbar .btn-secondary:hover {
            background: #334155;
        }

        /* A4 Page Container */
        .paper-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 22mm 22mm 20mm 22mm;
            position: relative;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .page-content {
            flex: 1;
        }

        /* Typography & Layout */
        .header-title-main {
            font-size: 23px;
            font-weight: 800;
            text-align: center;
            letter-spacing: -0.2px;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .header-subtitle {
            font-size: 12px;
            text-align: center;
            color: #475569;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .agreement-title {
            font-size: 19px;
            font-weight: 800;
            text-align: center;
            color: #0f172a;
            margin: 10px 0 16px 0;
            padding-bottom: 4px;
        }

        .preamble-text {
            font-size: 13px;
            text-align: justify;
            margin-bottom: 14px;
            line-height: 1.6;
        }

        /* Two-Column Parties Box */
        .parties-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1.2px solid #334155;
        }

        .parties-table th {
            background: #f8fafc;
            border: 1.2px solid #334155;
            padding: 6px 10px;
            font-size: 13px;
            font-weight: 800;
            text-align: left;
        }

        .parties-table td {
            border: 1.2px solid #334155;
            padding: 7px 10px;
            font-size: 12.5px;
            vertical-align: top;
            width: 50%;
            line-height: 1.55;
        }

        .parties-table .school-name-text {
            font-weight: 700;
        }

        /* Section Headings */
        .sec-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 14px;
            margin-bottom: 6px;
        }

        .sec-body {
            font-size: 13px;
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .bullet-list {
            list-style: none;
            margin-left: 4px;
            margin-bottom: 10px;
        }

        .bullet-list li {
            position: relative;
            padding-left: 14px;
            margin-bottom: 5px;
            font-size: 12.8px;
            line-height: 1.55;
            text-align: justify;
        }

        .bullet-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            top: 0;
            font-weight: bold;
        }

        /* Fee Structure Table */
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px 0;
            border: 1.2px solid #334155;
        }

        .fee-table th {
            border: 1.2px solid #334155;
            padding: 6px 10px;
            background: #f8fafc;
            font-size: 12.5px;
            font-weight: 800;
            text-align: left;
        }

        .fee-table td {
            border: 1.2px solid #334155;
            padding: 6px 10px;
            font-size: 12.5px;
        }

        /* Signatures Table */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            border: 1.2px solid #334155;
        }

        .sig-table th {
            border: 1.2px solid #334155;
            padding: 6px 10px;
            background: #f8fafc;
            font-size: 12.5px;
            font-weight: 800;
            text-align: left;
            width: 50%;
        }

        .sig-table td {
            border: 1.2px solid #334155;
            padding: 8px 10px;
            font-size: 12.5px;
            vertical-align: top;
            width: 50%;
            line-height: 1.8;
        }

        .witness-header {
            font-size: 13px;
            font-weight: 800;
            margin-top: 14px;
            margin-bottom: 4px;
        }

        /* Running Footer */
        .running-footer {
            border-top: 0.8px solid #cbd5e1;
            padding-top: 6px;
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5px;
            color: #64748b;
        }

        .running-footer .center-title {
            margin: 0 auto;
            text-align: center;
            font-weight: 600;
            color: #475569;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
            }

            .no-print-toolbar {
                display: none !important;
            }

            .paper-sheet {
                width: 100%;
                min-height: 100vh;
                margin: 0;
                padding: 16mm 18mm 16mm 18mm;
                box-shadow: none;
                page-break-after: always;
                break-after: page;
            }

            .paper-sheet:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Action Bar (Hidden during print) --}}
    <div class="no-print-toolbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('super.agreements.show', $agreement->id) }}" class="btn btn-secondary">
                &larr; Back to Agreement Details
            </a>
            <span style="font-weight: 700; font-size: 14px;">
                {{ $agreement->agreement_number }} &bull; {{ $agreement->school_name }}
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button onclick="window.print()" class="btn">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
                Print Official Agreement (हस्ताक्षर सम्झौता प्रिन्ट गर्नुहोस्)
            </button>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════
         PAGE 1 / पृष्ठ १ (Header, Preamble, Parties Table, Sec 1 & 2)
    ══════════════════════════════════════════════════════════════ --}}
    <div class="paper-sheet">
        <div class="page-content">
            <h1 class="header-title-main">नेपाल स्कुल हब</h1>
            <p class="header-subtitle">टेक प्रोकोड प्रा. लि. | सिरहा / काठमाडौँ, नेपाल</p>
            <h2 class="agreement-title">विद्यालय व्यवस्थापन प्रणालीसम्बन्धी सेवा सम्झौता</h2>

            <p class="preamble-text">
                यो सेवा सम्झौता मिति <strong>{{ $agreement->agreement_date_bs }}</strong> का दिन (“लागू हुने मिति”) मा देहायका दुई पक्षबीच आपसी सहमतिमा सम्पन्न भएको हो। टेक प्रोकोड प्रा. लि. प्रचलित कानुनबमोजिम दर्ता भई सञ्चालनमा रहेको संस्था हो र “नेपालस्कुलहब” उक्त कम्पनीको स्वामित्वमा रहेको ब्रान्ड तथा उत्पादन हो।
            </p>

            {{-- Two-Column Parties Box --}}
            <table class="parties-table">
                <thead>
                    <tr>
                        <th>सेवा प्रदायक</th>
                        <th>सेवा लिने विद्यालय</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $agreement->provider_name }}</strong><br>
                            कम्पनी दर्ता नं.: {{ $agreement->provider_reg_no }}<br>
                            स्थायी लेखा नं. (प्यान/भ्याट): {{ $agreement->provider_pan_vat }}<br>
                            फोन: {{ $agreement->provider_phone }}<br>
                            इमेल: {{ $agreement->provider_email }}<br>
                            वेबसाइट: {{ $agreement->provider_website }}
                        </td>
                        <td>
                            <span class="school-name-text">विद्यालयको नाम: {{ $agreement->school_name }}</span><br>
                            ठेगाना: {{ $agreement->school_address }}<br>
                            @if($agreement->school_reg_no) कम्पनी दर्ता नं.: {{ $agreement->school_reg_no }}<br>@endif
                            @if($agreement->school_pan_no) स्थायी लेखा नं. (प्यान): {{ $agreement->school_pan_no }}<br>@endif
                            अधिकृत प्रतिनिधि: {{ $agreement->school_representative ?: '………………………………………' }}<br>
                            पद: {{ $agreement->school_designation ?: '………………………………………' }}<br>
                            फोन / इमेल: {{ $agreement->school_phone ?: '…………………………………' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- १. उद्देश्य तथा सेवाको दायरा --}}
            <div class="sec-title">१. उद्देश्य तथा सेवाको दायरा</div>
            <p class="sec-body">
                सेवा प्रदायकले यस सम्झौतामा उल्लिखित सर्त तथा व्यवस्थाको अधीनमा रही सेवा लिने विद्यालयलाई “नेपालस्कुलहब” विद्यालय व्यवस्थापन प्रणाली र त्यससँग सम्बन्धित डिजिटल सेवाहरू उपलब्ध गराउनेछ।
            </p>
            <p class="sec-body">
                उक्त सेवाअन्तर्गत विद्यार्थी तथा कर्मचारीको अभिलेख व्यवस्थापन, अनलाइन हाजिरी, कक्षा तथा परीक्षा व्यवस्थापन, शुल्क अभिलेख, सूचना प्रवाह, प्रगति प्रतिवेदन, अभिभावकसँगको सञ्चार तथा प्रणालीमा उपलब्ध अन्य डिजिटल सुविधाहरू रहनेछन्। विद्यालयले प्रयोग गर्न पाउने सेवा तथा प्रणालीका अङ्गहरू दुवै पक्षबीच सहमत भएको प्रारम्भिक व्यवस्था र प्रणालीको प्राविधिक क्षमतामा निर्भर रहनेछन्। विद्यालयका लागि प्रयोग गरिने डोमेन तथा उपडोमेन, मोबाइल अनुप्रयोग विकासकर्ता खाता र सम्पूर्ण प्रणालीसम्बन्धी बौद्धिक सम्पत्ति अधिकार सेवा प्रदायकमै निहित रहनेछ।
            </p>

            {{-- २. शुल्क तथा मूल्य संरचना --}}
            <div class="sec-title">२. शुल्क तथा मूल्य संरचना</div>
            <p class="sec-body">दुवै पक्ष देहायबमोजिमको सेवा तथा मूल्य संरचनामा सहमत भएका छन्:</p>

            <table class="fee-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">सेवा वा वस्तुको विवरण</th>
                        <th style="width: 22%;">मूल्य (रु.)</th>
                        <th style="width: 28%;">शुल्क लाग्ने आधार</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>पहिलो वर्षको प्रारम्भिक स्थापना तथा प्रणाली सञ्चालन शुल्क</td>
                        <td><strong>{{ number_format($agreement->setup_fee) }}/-</strong></td>
                        <td>{{ $agreement->setup_fee_basis }}</td>
                    </tr>
                    <tr>
                        <td>वार्षिक नवीकरण शुल्क</td>
                        <td><strong>{{ number_format($agreement->renewal_fee) }}/-</strong></td>
                        <td>{{ $agreement->renewal_fee_basis }}</td>
                    </tr>
                    <tr>
                        <td>विद्यालय व्यवस्थापन सफ्टवेयर, मोबाइल अनुप्रयोग तथा वेबसाइट</td>
                        <td><strong>समावेश</strong></td>
                        <td>{{ $agreement->software_inclusion_text }}</td>
                    </tr>
                    @foreach($agreement->items as $it)
                        <tr>
                            <td>
                                <strong>{{ $it->item_name }}</strong>
                                @if($it->quantity > 1) (परिमाण: {{ $it->quantity }}) @endif
                            </td>
                            <td><strong>{{ number_format($it->total_price) }}/-</strong></td>
                            <td>{{ $it->basis }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="running-footer">
            <span class="center-title">नेपालस्कुलहब — विद्यालय व्यवस्थापन प्रणालीसम्बन्धी सेवा सम्झौता</span>
            <span>पृष्ठ १</span>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════
         PAGE 2 / पृष्ठ २ (Sec 2.1, 2.2, 3, 4, 5)
    ══════════════════════════════════════════════════════════════ --}}
    <div class="paper-sheet">
        <div class="page-content">
            <div class="sec-title">२.१ भुक्तानीका सर्त तथा करसम्बन्धी व्यवस्था</div>
            <ul class="bullet-list">
                <li>पहिलो वर्षको प्रारम्भिक स्थापना तथा प्रणाली सञ्चालन शुल्क रु. <strong>{{ number_format($agreement->setup_fee) }}/-</strong> सम्झौतामा हस्ताक्षर गर्दा वा प्रणाली हस्तान्तरण गर्नुअघि भुक्तानी गर्नुपर्नेछ।</li>
                <li>दोस्रो वर्षदेखि लागू हुने वार्षिक नवीकरण शुल्क रु. <strong>{{ number_format($agreement->renewal_fee) }}/-</strong> प्रत्येक वर्षको प्रारम्भमा भुक्तानी गर्नुपर्नेछ।</li>
                <li>उल्लिखित मूल्यमा प्रचलित कानुनबमोजिम लाग्ने कर तथा मूल्य अभिवृद्धि कर समावेश भएको वा छुट्टै लाग्ने हुन सक्नेछ।</li>
                <li>विद्यालयको पूर्वलिखित स्वीकृतिबिना कुनै अतिरिक्त तेस्रो पक्षीय शुल्क वा अप्रत्यक्ष लागत विद्यालयलाई बिल गरिने छैन।</li>
            </ul>

            <div class="sec-title">२.२ थप सुविधा, परिमार्जन तथा विशेष विकाससम्बन्धी शुल्क</div>
            <p class="sec-body">
                विद्यालयले यस सम्झौतामा उल्लेख गरिएका सुविधाबाहेक थप नयाँ सुविधा, विशेष कार्यक्षमता, प्रणालीमा परिमार्जन वा विद्यालयको आवश्यकताअनुसार छुट्टै सफ्टवेयर विकास गराउन चाहेमा त्यसका लागि लाग्ने अतिरिक्त शुल्क दुवै पक्षबीच आपसी छलफल तथा सहमतिका आधारमा निर्धारण गरिनेछ।
            </p>
            <p class="sec-body">
                थप सुविधा वा विशेष विकास कार्य सुरु गर्नुअघि सेवा प्रदायकले सम्बन्धित कार्यको प्रकृति, कार्यक्षेत्र, अनुमानित समय तथा लाग्ने शुल्कसहितको प्रस्ताव विद्यालयसमक्ष पेस गर्नुपर्नेछ। विद्यालयबाट लिखित स्वीकृति प्राप्त भएपछि मात्र त्यस्तो कार्य अघि बढाइनेछ। यसरी विकास वा थप गरिएका सुविधाका लागि लाग्ने शुल्क मूल सम्झौतामा उल्लिखित स्थापना तथा वार्षिक नवीकरण शुल्कभन्दा छुट्टै हुनेछ।
            </p>

            <div class="sec-title">३. प्रणालीमा पहुँच तथा प्रयोग</div>
            <p class="sec-body">
                सेवा प्रदायकले सहमत सेवाहरू सञ्चालन गर्न विद्यालयलाई आवश्यक प्रणालीगत पहुँच, प्रशासक खाताको विवरण तथा प्रारम्भिक प्रयोगसम्बन्धी अभिमुखीकरण उपलब्ध गराउनेछ। खाताको गोपनीयता कायम राख्ने, पहुँचसम्बन्धी विवरण सुरक्षित राख्ने र विद्यालयका अधिकृत कर्मचारीबाट प्रणालीको उचित, मर्यादित तथा कानुनसम्मत प्रयोग भएको सुनिश्चित गर्ने जिम्मेवारी विद्यालयको हुनेछ।
            </p>

            <div class="sec-title">४. मर्मतसम्भार तथा प्राविधिक सहयोग</div>
            <p class="sec-body">
                सेवा प्रदायकले प्रणालीको नियमित सञ्चालनका लागि आवश्यक प्राविधिक सहयोग तथा मर्मतसम्भार उपलब्ध गराउनेछ। प्राविधिक सहयोग सामान्य कार्यालय समयमा, अर्थात् आइतबारदेखि शुक्रबारसम्म बिहान १०:०० बजेदेखि साँझ ५:०० बजेसम्म उपलब्ध हुनेछ। प्रणालीमा देखिएको प्राविधिक समस्या वा सोसम्बन्धी जिज्ञासामा सेवा प्रदायकले २४ घण्टाभित्र प्रारम्भिक प्रतिक्रिया दिनेछ।
            </p>
            <p class="sec-body">
                पूर्वनिर्धारित मर्मतसम्भार गर्नुपर्ने भएमा सेवा प्रदायकले विद्यालयलाई अग्रिम जानकारी दिनेछ। प्रणालीको नियमित स्तरोन्नति वा मर्मतसम्भार, इन्टरनेट सेवामा आएको समस्या, तेस्रो पक्षीय सेवा प्रदायकका कारण उत्पन्न अवरोध वा सेवा प्रदायकको नियन्त्रणबाहिरका परिस्थितिले सेवा अस्थायी रूपमा अवरुद्ध हुन सक्नेछ। यस्तो अवस्थामा सेवा प्रदायकले यथाशीघ्र सेवा पुनः सञ्चालन गर्न आवश्यक पहल गर्नेछ।
            </p>

            <div class="sec-title">५. दुवै पक्षका जिम्मेवारी</div>
            <p class="sec-body">
                <strong>सेवा प्रदायकको जिम्मेवारी:</strong> यस सम्झौताबमोजिमका सेवाहरू उपलब्ध गराउने, आवश्यक प्राविधिक सहयोग प्रदान गर्ने तथा प्रणालीमा सङ्ग्रहित विद्यालयका तथ्याङ्क सुरक्षित राख्न उपयुक्त सुरक्षा उपाय अवलम्बन गर्ने। साथै, व्यक्तिगत गोपनीयतासम्बन्धी ऐन, २०७५ लगायत लागू हुने प्रचलित कानुनका प्रावधान पालना गर्ने।
            </p>
            <p class="sec-body">
                <strong>विद्यालयको जिम्मेवारी:</strong> प्रणाली सञ्चालनका लागि सही, पूर्ण तथा तथ्यपरक जानकारी उपलब्ध गराउने, अधिकृत प्रयोगकर्ता तोक्ने र प्रणालीमा प्रविष्ट गरिने तथ्याङ्क कानुनसम्मत रहेको सुनिश्चित गर्ने। विद्यार्थी, अभिभावक तथा कर्मचारीका व्यक्तिगत तथ्याङ्क सङ्कलन वा प्रयोग गर्न आवश्यक पूर्वस्वीकृति वा सहमति प्राप्त गर्ने सम्पूर्ण जिम्मेवारी विद्यालयको हुनेछ।
            </p>
        </div>

        <div class="running-footer">
            <span class="center-title">नेपालस्कुलहब — विद्यालय व्यवस्थापन प्रणालीसम्बन्धी सेवा सम्झौता</span>
            <span>पृष्ठ २</span>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════
         PAGE 3 / पृष्ठ ३ (Sec 6, 7, 8, 9, 10, 11)
    ══════════════════════════════════════════════════════════════ --}}
    <div class="paper-sheet">
        <div class="page-content">
            <div class="sec-title">६. विद्यालयका तथ्याङ्क तथा गोपनीयता</div>
            <p class="sec-body">
                प्रणालीमा प्रविष्ट गरिएका सम्पूर्ण तथ्याङ्कको स्वामित्व विद्यालयमै रहनेछ। सेवा प्रदायकले सेवा सञ्चालन, प्राविधिक मर्मतसम्भार, सुरक्षा तथा प्राविधिक सहयोग प्रदान गर्ने प्रयोजनका लागि मात्र, वा विद्यालयको स्पष्ट सहमति प्राप्त भएको अवस्थामा अथवा कानुनले बाध्य पारेको अवस्थामा मात्र त्यस्ता तथ्याङ्क प्रयोग गर्न सक्नेछ।
            </p>
            <p class="sec-body">
                दुवै पक्षले एकअर्काबाट प्राप्त गोप्य जानकारीको गोपनीयता कायम राख्नेछन्। सम्झौता समाप्त वा अन्त्य भएको मितिले ३० देखि ६० दिनभित्र विद्यालयले अनुरोध गरेमा सेवा प्रदायकले विद्यालयका तथ्याङ्क निःशुल्क रूपमा एक्सेल वा सीएसभी ढाँचामा उपलब्ध गराउनेछ। उक्त अवधिपछि सेवा प्रदायकले विद्यालयका तथ्याङ्क आफ्नो सर्भरबाट सुरक्षित रूपमा हटाउन सक्नेछ।
            </p>

            <div class="sec-title">७. बौद्धिक सम्पत्ति</div>
            <p class="sec-body">
                नेपालस्कुलहब प्रणाली, सफ्टवेयरको स्रोत सङ्केत, डिजाइन, ब्रान्ड, दृश्य पहिचान तथा यससँग सम्बन्धित सम्पूर्ण बौद्धिक सम्पत्तिको स्वामित्व सेवा प्रदायकमै निहित रहनेछ। यस सम्झौताको अवधिभर विद्यालयले आफ्नो आन्तरिक शैक्षिक तथा प्रशासनिक कार्यका लागि मात्र प्रणाली प्रयोग गर्न पाउनेछ। यस्तो प्रयोग अधिकार अनन्यधिकाररहित र अरूलाई हस्तान्तरण गर्न नमिल्ने हुनेछ।
            </p>

            <div class="sec-title">८. तेस्रो पक्षीय सेवा तथा दायित्वको सीमा</div>
            <p class="sec-body">
                केही विशेष सुविधा, जस्तै सन्देश पठाउने प्रणाली वा सर्भरमा सेवा राख्ने व्यवस्था, तेस्रो पक्षले उपलब्ध गराउने सेवामा निर्भर हुन सक्नेछन्। त्यस्ता सेवाका लागि विद्यालयले कुनै अतिरिक्त शुल्क व्यहोर्नुपर्ने भएमा सेवा प्रदायकले पहिले नै जानकारी दिई विद्यालयको लिखित स्वीकृति प्राप्त गर्नुपर्नेछ।
            </p>
            <p class="sec-body">
                प्रचलित कानुनले अनुमति दिएको हदसम्म, प्रणालीको प्रयोग वा अस्थायी अनुपलब्धताका कारण उत्पन्न हुन सक्ने अप्रत्यक्ष वा परिणामस्वरूप हुने क्षतिप्रति सेवा प्रदायकको कुल दायित्व विद्यालयले त्यसअघिका १२ महिनामा भुक्तानी गरेको कुल रकममा सीमित रहनेछ।
            </p>

            <div class="sec-title">९. सम्झौताको अवधि, नवीकरण तथा समाप्ति</div>
            <p class="sec-body">
                यो सम्झौता लागू हुने मितिदेखि <strong>{{ $agreement->duration_years }} ({{ $agreement->duration_years == 1 ? 'एक' : 'दुई' }})</strong> वर्षसम्म कायम रहनेछ। एक वर्षको अवधि समाप्त हुनुअघि सम्झौता अन्त्य गर्न वा नवीकरण नगर्न चाहने पक्षले कम्तीमा ६० (साठी) दिन अगावै अर्को पक्षलाई लिखित सूचना दिनुपर्नेछ।
            </p>
            <p class="sec-body">
                प्रत्येक वर्ष तोकिएको वार्षिक नवीकरण शुल्क रु. <strong>{{ number_format($agreement->renewal_fee) }}/-</strong> भुक्तानी गरी सम्झौता नवीकरण गर्न सकिनेछ। एक वर्षको अवधि समाप्त भएपछि औपचारिक लिखित नवीकरणसम्बन्धी छलफल भइरहेको अवस्थामा ६० (साठी) दिनको थप सहुलियत अवधि कायम रहनेछ।
            </p>
            <p class="sec-body">
                कुनै पनि पक्षले अर्को पक्षलाई ३० (तीस) दिनको पूर्वलिखित सूचना दिई यो सम्झौता अन्त्य गर्न सक्नेछ। कुनै पक्षले सम्झौताका महत्त्वपूर्ण सर्त उल्लङ्घन गरेमा र लिखित सूचना प्राप्त भएको ३० (तीस) दिनभित्र त्यस्तो उल्लङ्घन सच्याउन नसकेमा प्रभावित पक्षले सम्झौता अन्त्य गर्न सक्नेछ।
            </p>

            <div class="sec-title">१०. लागू हुने कानुन तथा विवाद समाधान</div>
            <p class="sec-body">
                यो सम्झौताको व्याख्या तथा कार्यान्वयन नेपालको प्रचलित कानुनबमोजिम हुनेछ। सम्झौताको कार्यान्वयनका क्रममा कुनै विवाद उत्पन्न भएमा दुवै पक्षले आपसी छलफल तथा मेलमिलापमार्फत समाधान गर्ने प्रयास गर्नेछन्। त्यसरी समाधान हुन नसकेमा मध्यस्थता ऐन, २०५५ बमोजिम मध्यस्थतामार्फत विवाद समाधान गरिनेछ। आवश्यक कानुनी कारबाहीका लागि नेपालको अधिकारप्राप्त अदालतको क्षेत्राधिकार कायम रहनेछ।
            </p>

            <div class="sec-title">११. सामान्य सर्तहरू</div>
            <p class="sec-body">
                यो सम्झौताले यसमा उल्लिखित विषयका सम्बन्धमा दुवै पक्षबीच भएको सम्पूर्ण समझदारीलाई जनाउँछ। यस सम्झौतामा कुनै संशोधन वा थप सर्त समावेश गर्नुपरेमा दुवै पक्षका अधिकृत प्रतिनिधिले लिखित रूपमा स्वीकृत गरेको हुनुपर्नेछ।
            </p>
        </div>

        <div class="running-footer">
            <span class="center-title">नेपालस्कुलहब — विद्यालय व्यवस्थापन प्रणालीसम्बन्धी सेवा सम्झौता</span>
            <span>पृष्ठ ३</span>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════
         PAGE 4 / पृष्ठ ४ (Sec 12, 13 & Signatures)
    ══════════════════════════════════════════════════════════════ --}}
    <div class="paper-sheet">
        <div class="page-content">
            <p class="sec-body">
                कुनै पक्षको उचित नियन्त्रणबाहिरको परिस्थिति (दैवी प्रकोप, प्राकृतिक विपत्ति वा अन्य अपरिहार्य घटना) का कारण दायित्व पूरा गर्न ढिलाइ भएमा सो ढिलाइका लागि सम्बन्धित पक्ष जिम्मेवार हुने छैन।
            </p>

            {{-- १२. सहमत मूल्यको पुष्टि --}}
            <div class="sec-title">१२. सहमत मूल्यको पुष्टि</div>
            <p class="sec-body">
                दुवै पक्षले हस्ताक्षर गरी देहायका मूल्य तथा सर्तहरू पढी, बुझी र स्वीकार गरेको पुष्टि गर्दछन्:
            </p>
            <ul class="bullet-list">
                <li>पहिलो वर्षको प्रारम्भिक स्थापना तथा प्रणाली सञ्चालन शुल्क: <strong>रु. {{ number_format($agreement->setup_fee) }}/-</strong></li>
                <li>वार्षिक नवीकरण शुल्क (दोस्रो वर्षदेखि): <strong>रु. {{ number_format($agreement->renewal_fee) }}/-</strong></li>
                <li>विद्यालय व्यवस्थापन सफ्टवेयर, मोबाइल अनुप्रयोग तथा वेबसाइट: <strong>माथि उल्लिखित शुल्कमा समावेश</strong></li>
                @foreach($agreement->items as $it)
                    <li>{{ $it->item_name }}: <strong>रु. {{ number_format($it->total_price) }}/-</strong> (परिमाण: {{ $it->quantity }}, आधार: {{ $it->basis }})</li>
                @endforeach
                @if($agreement->items->count() > 0)
                    <li>सहमति भएको कुल रकम: <strong>रु. {{ number_format($agreement->grand_total) }}/-</strong></li>
                @endif
            </ul>

            {{-- १३. हस्ताक्षर तथा स्वीकृति --}}
            <div class="sec-title">१३. हस्ताक्षर तथा स्वीकृति</div>
            <p class="sec-body">
                तल हस्ताक्षर गर्ने व्यक्तिहरू आआफ्नो संस्था वा पक्षका तर्फबाट यस सम्झौतामा हस्ताक्षर गर्न विधिवत् अधिकृत रहेको पुष्टि गर्दछन्।
            </p>

            <table class="sig-table">
                <thead>
                    <tr>
                        <th>सेवा प्रदायकका तर्फबाट</th>
                        <th>विद्यालयका तर्फबाट</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 155px;">
                            नाम: {{ $agreement->provider_representative ?: '………………………………………' }}<br>
                            पद: {{ $agreement->provider_designation ?: '………………………………………' }}<br><br>
                            हस्ताक्षर: …………………………………<br><br>
                            मिति: ………………………………………<br><br>
                            छाप:
                        </td>
                        <td style="height: 155px;">
                            विद्यालयको नाम: <strong>{{ $agreement->school_name }}</strong><br>
                            अधिकृत प्रतिनिधिको नाम: {{ $agreement->school_representative ?: '…………………………………' }}<br>
                            पद: {{ $agreement->school_designation ?: '………………………………………' }}<br><br>
                            हस्ताक्षर: …………………………………<br><br>
                            मिति: ………………………………………<br><br>
                            छाप:
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- साक्षीहरू --}}
            <div class="witness-header">साक्षीहरू</div>
            <table class="sig-table">
                <thead>
                    <tr>
                        <th>साक्षी १ (सेवा प्रदायक)</th>
                        <th>साक्षी २ (विद्यालय)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="height: 110px;">
                            नाम: {{ $agreement->witness_1_name ?: '…………………………………' }}<br><br>
                            हस्ताक्षर: ……………………………<br><br>
                            मिति: {{ $agreement->witness_1_date ?: '…………………………………' }}
                        </td>
                        <td style="height: 110px;">
                            नाम: {{ $agreement->witness_2_name ?: '…………………………………' }}<br><br>
                            हस्ताक्षर: ……………………………<br><br>
                            मिति: {{ $agreement->witness_2_date ?: '…………………………………' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="running-footer">
            <span class="center-title">नेपालस्कुलहब — विद्यालय व्यवस्थापन प्रणालीसम्बन्धी सेवा सम्झौता</span>
            <span>पृष्ठ ४</span>
        </div>
    </div>

</body>
</html>

