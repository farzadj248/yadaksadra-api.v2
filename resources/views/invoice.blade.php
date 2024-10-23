<!DOCTYPE html>
<html dir="rtl" lang="fa">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php if( $data['isPost']==false){?>
        فاکتور خرید | {{ $data['order_code'] }}
        <?php }else{?>
        آدرس فرستنده و گیرنده برای سفارش {{$data['order_code']}}
        <?php }?>
    
    </title>
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #0087C3;
            text-decoration: none;
        }

        h1 {
            font-size: 14px;
        }

        h2 {
            font-size: 10px
        }

        p {
            font-size: 10px
        }

        table tbody tr td{
            line-height: 1.9
        }

        body {
            position: relative;
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-family: SourceSansPro;
        }

        header {
            padding: 0;
            margin-bottom: 0px;
            border: 0;
        }

        .table-border tr td {
            padding: 8px;
            border: 1px solid rgba(243, 243, 243, 0.986)
        }

        #company {
            float: right;
            text-align: right;
        }


        #details {
            margin-bottom: 20px;
        }

        #client {
            float: right;
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1.4em;
            font-weight: normal;
            margin: 0;
        }

        #invoice {
            float: left;
            text-align: left;
            margin-right: auto;
            margin-left: 0;
        }

        #invoice h1 {
            color: #0087C3;
            font-size: 2.4em;
            line-height: 1em;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
            font-size: 10px;
            vertical-align: middle
        }

        table th,
        table td {
            padding: 8px;
            background: #EEEEEE;
            text-align: center;
            border-bottom: 1px solid #FFFFFF;
        }

        table th {
            white-space: nowrap;
            font-weight: normal;
        }

        table td {
            text-align: right;
        }

        table td h3 {
            color: #fdb817;
            font-size: 1.2em;
            font-weight: normal;
            margin: 0 0 0.2em 0;
        }

        table .no {
            color: #FFFFFF;
            font-size: 1em;
            background: #fdb817;
            text-align: center
        }

        table .desc {
            text-align: right;
        }

        table .unit {
            background: #DDDDDD;
            text-align: center
        }

        table .qty {}

        table .total {
            background: #fdb817;
            color: #FFFFFF;
            text-align: center
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1.2em;
        }

        table tbody tr:last-child td {
            border: none;
        }

        table tfoot td {
            padding: 10px 20px;
            background: #FFFFFF;
            border-bottom: none;
            font-size: 1.2em;
            white-space: nowrap;
            border-top: 1px solid #eee;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr:last-child td {
            color: #fdb817;
            font-size: 1.4em;
            border-top: 1px solid #fdb817;

        }

        table tfoot tr td:first-child {
            border: none;
        }

        #thanks {
            font-size: 2em;
            margin-bottom: 50px;
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #0087C3;
        }

        #notices .notice {
            font-size: 1.2em;
        }

        .invoice-footer {
            width: 250px;
            float: left
        }
        .invoice-footer p{
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #AAAAAA;
            padding: 8px 0;
            text-align: center;
        }

        .d-flex {
            display: flex;
        }

        .mr-0 {
            margin-right: 0;
        }

        .ml-auto {
            margin-left: auto;
        }

        .row {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col-6 {
            -ms-flex: 0 0 50%;
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col,
        .col-1,
        .col-10,
        .col-11,
        .col-12,
        .col-2,
        .col-3,
        .col-4,
        .col-5,
        .col-6,
        .col-7,
        .col-8,
        .col-9,
        .col-auto,
        .col-lg,
        .col-lg-1,
        .col-lg-10,
        .col-lg-11,
        .col-lg-12,
        .col-lg-2,
        .col-lg-3,
        .col-lg-4,
        .col-lg-5,
        .col-lg-6,
        .col-lg-7,
        .col-lg-8,
        .col-lg-9,
        .col-lg-auto,
        .col-md,
        .col-md-1,
        .col-md-10,
        .col-md-11,
        .col-md-12,
        .col-md-2,
        .col-md-3,
        .col-md-4,
        .col-md-5,
        .col-md-6,
        .col-md-7,
        .col-md-8,
        .col-md-9,
        .col-md-auto,
        .col-sm,
        .col-sm-1,
        .col-sm-10,
        .col-sm-11,
        .col-sm-12,
        .col-sm-2,
        .col-sm-3,
        .col-sm-4,
        .col-sm-5,
        .col-sm-6,
        .col-sm-7,
        .col-sm-8,
        .col-sm-9,
        .col-sm-auto,
        .col-xl,
        .col-xl-1,
        .col-xl-10,
        .col-xl-11,
        .col-xl-12,
        .col-xl-2,
        .col-xl-3,
        .col-xl-4,
        .col-xl-5,
        .col-xl-6,
        .col-xl-7,
        .col-xl-8,
        .col-xl-9,
        .col-xl-auto {
            position: relative;
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .p-0 {
            padding: 0;
        }

        .m-0 {
            margin: 0;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
        
    </style>
</head>

<body>

    <?php if( $data['isPost']==false){?>
    <header class="clearfix d-flex p-0">
        <table border="0" cellspacing="0" cellpadding="0" class="m-0">
            <tbody style="padding:0;margin:0">
                <tr>
                    <td style="background: #fff;width:35%;padding:8px;" class="p-0">
                        <div id="logo">
                            <img src="logo.png" width="75">
                        </div>
                    </td>

                    <td style="background: #fff;text-align:center;width:30%;padding:8px;">
                        <h1>فاکتور خرید</h1>
                    </td>

                    <td style="background: #fff;font-size:10px; text-align:left;width:35%">
                        <div>
                            <h2 class="m-0">شماره فاکتور: {{ $data['order']['order_code'] }}</h2>
                            <div>تاریخ و ساعت: {{ $data['order']['created_at'] }}</div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </header>

    <main>
        <table class="table-border" style="margin-bottom:2rem;">
            <thead style="padding: 0">
                <tr>
                    <th style="padding:8px;border-left:1px solid #fffffff8">فروشنده</th>
                    <th style="padding:8px;">خریدار</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background: #fff;width:50%;">
                        <div id="client">
                            <h2 class="name" style="color: #fdb817">یدک صدرا</h2>
                            <div class="address">اصفهان،اصفهان،میدان تاکسیرانی، منطقه صنعتی شهریار 15</div>
                            <div class="postal-code">کد پستی: 8341660148</div>
                            <div class="phone">تلفن: 03191620062</div>
                        </div>
                    </td>

                    <td style="background: #fff;width:50%;">
                        <div id="client">
                            <h2 class="name">{{ $data['user']['first_name'] }} {{ $data['user']['last_name'] }}</h2>
                            <div class="address">نشانی:
                                {{ $data['address']['province'] }}،
                                {{ $data['address']['city'] }}،
                                {{ $data['address']['address'] }}

                                @if ($data['address']['plaque'])
                                ،پلاک {{ $data['address']['plaque'] }}
                                @endif

                                @if ($data['address']['floor'])
                                ،طبقه {{ $data['address']['floor'] }}
                                @endif

                                @if ($data['address']['building_unit'])
                                ،واحد {{ $data['address']['building_unit'] }}
                                @endif
                            </div>
                            <div class="postal-code">کد پستی: {{ $data['order']['postal_code'] }}</div>
                            <div class="phone"><a href="tel: @php echo $data['user']['mobile_number'];@endphp">شماره تماس: {{ $data['user']['mobile_number'] }}</a></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <table border="0" cellspacing="0" cellpadding="0" class="text-right">
            <thead>
                <tr>
                    <th class="no">کد کالا</th>
                    <th class="desc">شرح کالا</th>
                    <th class="unit">تعداد</th>
                    <th class="qty">مبلغ واحد(تومان)</th>
                    <th class="total">مبلغ کل(تومان)</th>
                </tr>
            </thead>

            <tbody>
                @foreach ( $data['orderItems'] as $item)
                    <tr>
                        <td class="no">{{ $item['code'] }}</td>
                        <td class="desc">
                            <h3>{{ $item['title'] }}<h3>
                        </td>
                        <td class="unit">{{ $item['quantity'] }}</td>
                        <td class="qty">{{ number_format($item['saved_price']) }}</td>
                        <td class="total">{{ number_format($item['saved_price'] * $item['quantity'])}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="table-border" style="margin-bottom:0rem;">
            <thead style="padding: 0;">
                <tr>
                    <th style="text-align:right">روش پرداخت</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background: #fff; padding:8px;">
                        <p style="margin-bottom: 20px">
                            @switch($data['order']['gateway_pay'])
                                @case(1)
                                    پرداخت اینترنتی-پرداخت الکترونیک سداد
                                    @break
                                    
                                @case(2)
                                   پرداخت اینترنتی-به پرداخت ملت 
                                    @break
                                    
                                @case(3)
                                پرداخت آنلاین - کیف پول حساب کاربری
                                    @break
                                    
                                @case(6)
                                   پرداخت آنلاین - پرداخت اعتباری
                                    @break
                            @endswitch
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="invoice-footer">
            <p style="border-top:0">
                سود شما از خرید: {{number_format($data['order']['discount']) }} تومان
            </p>
            <p>
                هزینه ارسال: {{ number_format($data['order']['sending_amount']) }} تومان
            </p>
            <p style="color:green;font-size:14px">
                مجموع مبلغ نهایی: {{ number_format($data['order']['total']) }} تومان
            </p>
        </div>
    </main>

    <footer style="margin:0 auto">
        فاکتور توسط فروشگاه <a href="https://yadaksadra.com/">یدک صدرا</a> ایجاد شده است.
    </footer>
    <?php }else{?>
    
    <div id="postal-sheet">
        <div>
            <div style="font-size:22px;font-weight:bold">
                <h3>آدرس فرستنده:</h3>
            </div>
            <div style="font-size:18px;margin-bottom:0.7rem;">
                <div> اصفهان میدان تاکسیرانی ابتدای دولت آباد شهریار 15 بعد از سنگبری ایرانا پ163</div>
            </div>
            <div style="font-size:18px;margin-bottom:0.7rem;">
                <div>کد پستی 8341660148</div>
            </div>
            <div style="font-size:18px;margin-bottom:0.7rem;">
                <div>آقای فروتن 09134634403</div>
            </div>
        </div>
    </div>
                        
    <div id="postal-sheet" style="margin-top:2rem;">
        <div>
            <div style="font-size:22px;font-weight:bold">
                <h3>آدرس گیرنده:</h3>
            </div>
            <div style="font-size:18px;margin-bottom:0.7rem;">
                <div>
                {{ $data['address']['province'] }}،
                {{ $data['address']['city'] }}،
                {{ $data['address']['address'] }}

                @if ($data['address']['plaque'])
                ،پلاک {{ $data['address']['plaque'] }}
                @endif

                @if ($data['address']['floor'])
                ،طبقه {{ $data['address']['floor'] }}
                @endif

                @if ($data['address']['building_unit'])
                ،واحد {{ $data['address']['building_unit'] }}
                @endif
                </div>
            </div>
            <div style="font-size:18px;margin-bottom:0.7rem;">
                <div>کد پستی {{ $data['order']['postal_code'] }}</div>
            </div>
            <div style="font-size:18px;margin-bottom:0.7rem;">
                <div>{{ $data['user']['first_name'] }} {{ $data['user']['last_name'] }} {{ $data['user']['mobile_number'] }}</div>
            </div>
        </div>
    </div>
    
    <?php }?>
</body>

</html>
