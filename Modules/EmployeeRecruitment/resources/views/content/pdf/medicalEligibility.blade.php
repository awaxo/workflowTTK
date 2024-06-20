<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adatszolgáltatás Munkaalkalmassági Vizsgálathoz</title>
    <style>
        @page {
            margin: 1.2cm;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: right;
            margin-bottom: 15px;
            font-size: 0.5em;
        }
        
        .header .title {
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 0.8em;
            margin-top: 15px;
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
            padding: 4px;
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
            width: 4%;
        }
        
        .w_inv {
            width: 10%;
        }
        
        .w_desc {
            width: 36%;
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
            <tr>
                <td>2</td>
                <td>Fokozott baleseti veszély (tűz- és robbanásveszély, feszültség alatti munka, magasban végzett munka, egyéb)</td>
                <td class="inv">{{ $medical["increased_accident_risk"] == 'egesz' ? 'E' : ($medical["increased_accident_risk"] == 'resz' ? 'R' : '') }}</td>
                <td>15</td>
                <td>Vegyi anyagok</td>
                <td class="inv">{{ $medical["chemicals_exposure"] == 'egesz' ? 'E' : ($medical["chemicals_exposure"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>2.1</td>
                <td>Tűz- és robbanásveszély</td>
                <td class="inv">{{ $medical["fire_and_explosion_risk"] == 'egesz' ? 'E' : ($medical["fire_and_explosion_risk"] == 'resz' ? 'R' : '') }}</td>
                <td>15.1</td>
                <td>Kémiai kóroki tényezők: {{ implode(', ', $medical["chemical_hazards_exposure"]) }}</td>
                <td></td>
            </tr>
            <tr>
                <td>2.2</td>
                <td>Feszültség alatti munka</td>
                <td class="inv">{{ $medical["live_electrical_work"] == 'egesz' ? 'E' : ($medical["live_electrical_work"] == 'resz' ? 'R' : '') }}</td>
                <td>15.2</td>
                <td>Egyéb vegyi anyagok megnevezése: {{ $medical["other_chemicals_description"] }}</td>
                <td></td>
            </tr>
            <tr>
                <td>2.3</td>
                <td>Magasban végzett munka</td>
                <td class="inv">{{ $medical["high_altitude_work"] == 'egesz' ? 'E' : ($medical["high_altitude_work"] == 'resz' ? 'R' : '') }}</td>
                <td>15.3</td>
                <td>Rákkeltő anyagok</td>
                <td class="inv">{{ $medical["carcinogenic_substances_exposure"] == 'egesz' ? 'E' : ($medical["carcinogenic_substances_exposure"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>2.4</td>
                <td>Egyéb (fokozott baleseti veszéllyel járó kockázati tényező felsorolása)</td>
                <td class="inv">{{ $medical["fire_and_explosion_risk"] == 'egesz' ? 'E' : ($medical["fire_and_explosion_risk"] == 'resz' ? 'R' : '') }}</td>
                <td>15.4</td>
                <td>Használni tervezett rákkeltő anyagok felsorolása: {{ $medical["planned_carcinogenic_substances_list"] }}</td>
                <td></td>
            </tr>
            <tr>
                <td>3</td>
                <td>Kényszertesthelyzet (görnyedés, guggolás)</td>
                <td class="inv">{{ $medical["forced_body_position"] == 'egesz' ? 'E' : ($medical["forced_body_position"] == 'resz' ? 'R' : '') }}</td>
                <td>16</td>
                <td>Járványügyi érdekből kiemelt munkakör (egészségügyi könyvhöz kötött munkakör)</td>
                <td class="inv">{{ $medical["epidemiological_interest_position"] == 'egesz' ? 'E' : ($medical["epidemiological_interest_position"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>4</td>
                <td>Ülés</td>
                <td class="inv">{{ $medical["sitting"] == 'egesz' ? 'E' : ($medical["sitting"] == 'resz' ? 'R' : '') }}</td>
                <td>17</td>
                <td>Fertőzésveszély, biológiai kóroki tényezők (pl. leptospirózis, egyéb zoonozis, baktériumok, vér, szennyvíz stb.)</td>
                <td class="inv">{{ $medical["infection_risk"] == 'egesz' ? 'E' : ($medical["infection_risk"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>5</td>
                <td>Állás</td>
                <td class="inv">{{ $medical["standing"] == 'egesz' ? 'E' : ($medical["standing"] == 'resz' ? 'R' : '') }}</td>
                <td>18</td>
                <td>Fokozott pszichés terhelés (felelősség emberekért, anyagi értékekért, alkotó szellemi munka)</td>
                <td class="inv">{{ $medical["psychological_stress"] == 'egesz' ? 'E' : ($medical["psychological_stress"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>6</td>
                <td>Járás</td>
                <td class="inv">{{ $medical["walking"] == 'egesz' ? 'E' : ($medical["walking"] == 'resz' ? 'R' : '') }}</td>
                <td>19</td>
                <td>Képernyő előtt végzett munka (napi 4 óra vagy annál több)</td>
                <td class="inv">{{ $medical["screen_time"] == 'egesz' ? 'E' : ($medical["screen_time"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>7</td>
                <td>Terhelő munkahelyi klíma (meleg, hideg, nedves, változó)</td>
                <td class="inv">{{ $medical["stressful_workplace_climate"] == 'egesz' ? 'E' : ($medical["stressful_workplace_climate"] == 'resz' ? 'R' : '') }}</td>
                <td>20</td>
                <td>Éjszakai műszakban végzett munka</td>
                <td class="inv">{{ $medical["night_shift_work"] == 'egesz' ? 'E' : ($medical["night_shift_work"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>7.1</td>
                <td>Hőexpozíció (a munkahelyi hőmérséklet meghaladja a 24 °C korrigált effektív hőmérsékletet)</td>
                <td class="inv">{{ $medical["heat_exposure"] == 'egesz' ? 'E' : ($medical["heat_exposure"] == 'resz' ? 'R' : '') }}</td>
                <td>21</td>
                <td>Pszichoszociális tényezők</td>
                <td class="inv">{{ $medical["psychosocial_factors"] == 'egesz' ? 'E' : ($medical["psychosocial_factors"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>7.2</td>
                <td>Hideg expozíció (zárt térben +10 °C alatti munkavégzés)</td>
                <td class="inv">{{ $medical["cold_exposure"] == 'egesz' ? 'E' : ($medical["cold_exposure"] == 'resz' ? 'R' : '') }}</td>
                <td>22</td>
                <td>Egyéni védőeszköz általi terhelés</td>
                <td class="inv">{{ $medical["personal_protective_equipment_stress"] == 'egesz' ? 'E' : ($medical["personal_protective_equipment_stress"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>8</td>
                <td>Zaj (85 dB Aeq felett)</td>
                <td class="inv">{{ $medical["noise_exposure"] == 'egesz' ? 'E' : ($medical["noise_exposure"] == 'resz' ? 'R' : '') }}</td>
                <td>23</td>
                <td>Családtól tartósan távol munkát végzők</td>
                <td class="inv">{{ $medical["work_away_from_family"] == 'egesz' ? 'E' : ($medical["work_away_from_family"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>9</td>
                <td>Ionizáló sugárzás</td>
                <td class="inv">{{ $medical["ionizing_radiation_exposure"] == 'egesz' ? 'E' : ($medical["ionizing_radiation_exposure"] == 'resz' ? 'R' : '') }}</td>
                <td>24</td>
                <td>Időskor (nyugdíj melletti munkavégzés)</td>
                <td class="inv">{{ $medical["working_alongside_pension"] == 'egesz' ? 'E' : ($medical["working_alongside_pension"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>10</td>
                <td>Nem-ionizáló sugárzás</td>
                <td class="inv">{{ $medical["non_ionizing_radiation_exposure"] == 'egesz' ? 'E' : ($medical["non_ionizing_radiation_exposure"] == 'resz' ? 'R' : '') }}</td>
                <td rowspan="4">25</td>
                <td rowspan="4">Egyéb egészségkárosító kockázatok megnevezése: {{ $medical["planned_other_health_risk_factors"] }}</td>
                <td rowspan="4" class="inv">{{ $medical["others"] == 'egesz' ? 'E' : ($medical["others"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>11</td>
                <td>Helyileg ható vibráció</td>
                <td class="inv">{{ $medical["local_vibration_exposure"] == 'egesz' ? 'E' : ($medical["local_vibration_exposure"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>12</td>
                <td>Egésztest-vibráció</td>
                <td class="inv">{{ $medical["whole_body_vibration_exposure"] == 'egesz' ? 'E' : ($medical["whole_body_vibration_exposure"] == 'resz' ? 'R' : '') }}</td>
            </tr>
            <tr>
                <td>13</td>
                <td>Ergonómiai tényezők</td>
                <td class="inv">{{ $medical["ergonomic_factors_exposure"] == 'egesz' ? 'E' : ($medical["ergonomic_factors_exposure"] == 'resz' ? 'R' : '') }}</td>
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
