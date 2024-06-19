<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adatszolgáltatás Munkaalkalmassági Vizsgálathoz</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
        }
        .header {
            text-align: right;
            margin-bottom: 20px;
            font-size: 0.5em;
        }
        .header .title {
            font-size: 0.8em;
            font-weight: bold;
        }
        .section-title {
            font-weight: bold;
            font-size: 0.8em;
            margin-top: 20px;
            text-align: center;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            white-space: normal;
        }
        .bordered th, .bordered td {
            border: 1px solid #bbb;
            padding: 5px;
            text-align: left;
            font-size: 0.5em;
        }
        .table-header th {
            background-color: #f0f0f0;
            text-align: center;
        }
        .personal-data td {
            padding: 2px;
            font-size: 0.6em;
        }
        .signature {
            margin-top: 30px;
            font-size: 0.6em;
        }
        .stamp {
            text-align: center;
            margin-top: 50px;
            font-size: 0.8em;
        }
        .checkboxes td {
            vertical-align: top;
        }
        .w_nr {
            width: 3%;
        }
        .w_inv {
            width: 10%;
        }
        .w_desc {
            width: 37%;
        }
        .inv {
            font-weight: bold;
            font-size: 0.8em;
            text-align: center !important;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="title">TERMÉSZETTUDOMÁNYI KUTATÓKÖZPONT</div>
        <div class="subtitle">GAZDASÁGI IGAZGATÓSÁG</div>
        <div>1117 BUDAPEST, MAGYAR TUDÓSOK KÖRÚTJA 2.</div>
        <div>LEVÉLCÍM: 1519 BUDAPEST, PF. 286.</div>
        <div>TELEFON:</div>
        <div>E-MAIL:</div>
        <div>www.ttk.hu</div>
    </div>

    <div class="section-title">Beutaló munkaköri orvosi alkalmassági vizsgálatra</div>

    <table class="table personal-data">
        <tbody>
            <tr>
                <td>A munkavállaló neve: <b>{{ $recruitment->name }}</b></td>
                <td>Született: <b>{{ $recruitment->birth_date }}</b></td>
            </tr>
            <tr>
                <td>Lakcíme: <b>{{ $recruitment->address }}</b></td>
            </tr>
            <tr>
                <td>Munkaköre: <b>{{ $recruitment->position->name }}</b></td>
                <td>TAJ száma: <b>{{ $recruitment->social_security_number }}</b></td>
            </tr>
            <tr>
                <td>FEOR:</td>
            </tr>
            <tr>
                <td>Egység megnevezés:</td>
            </tr>
            <tr>
                <td>A vizsgálat oka: munkába lépés előtti vizsgálat</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">A munkakör (munkahely) főbb egészségkárosító kockázatai</div>

    <table class="table bordered">
        <thead class="table-header">
            <tr>
                <th colspan="2">Kockázat</th>
                <th rowspan="2" class="w_inv">Érintettség mértéke</th>
                <th colspan="2">Kockázat</th>
                <th rowspan="2" class="w_inv">Érintettség mértéke</th>
            </tr>
            <tr>
                <th class="w_nr">jel</th>
                <th class="w_desc">megnevezés</th>
                <th class="w_nr">jel</th>
                <th class="w_desc">megnevezés</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Kézi anyagmozgatás</td>
                <td class="inv">{{ $medical["manual_handling"] == 'egesz' ? 'E' : ($medical["manual_handling"] == 'resz' ? 'R' : '') }}</td>
                <td rowspan="4">14</td>
                <td rowspan="4">Porok megnevezése: <b>{{ $medical["dust_exposure_description"] }}</b></td>
                <td rowspan="4">{{ $medical["dust_exposure"] == 'egesz' ? 'E' : ($medical["dust_exposure"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>1.1</td>
                <td>5 kp - 20 kp</td>
                <td class="inv">{{ $medical["manual_handling_weight_5_20"] == 'egesz' ? 'E' : ($medical["manual_handling_weight_5_20"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>1.2</td>
                <td>5 kp - 20 kp</td>
                <td class="inv">{{ $medical["manual_handling_weight_20_50"] == 'egesz' ? 'E' : ($medical["manual_handling_weight_20_50"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>1.3</td>
                <td>5 kp - 20 kp</td>
                <td class="inv">{{ $medical["manual_handling_weight_over_50"] == 'egesz' ? 'E' : ($medical["manual_handling_weight_over_50"] == 'resz' ? 'R' : '') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        Budapest, {{ date('Y') }}.{{ date('m') }}.{{ date('d') }}.
    </div>
    <div class="stamp">
        P.H. <br>
        ..........................................................
    </div>

</body>
</html>
