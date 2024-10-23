<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <title>فروشگاه اینترنتی یدک صدرا | پرداخت صورتحساب سفارش</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
</head>

<style>
    @font-face {
      font-family: "iranSansBold";
      src: url("https://dl.yadaksadra.com/web/fonts/iransans/woff2/IRANSansWeb_Bold.woff2") format("woff2");
      src: url("https://dl.yadaksadra.com/web/fonts/iransans/woff/IRANSansWeb_Bold.woff") format("woff");
    }
    
    body {
      font: normal 300 1.4rem/1.86 "iranSans", "Poppins", sans-serif;
      color: #666;
      background-color: #fff;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      overflow-x: hidden
    }

    .payment-result_section {
        font-family: "iranSans";
        width: 300px;
        height: auto;
        margin: 4rem auto;
        padding: 2rem;
        border: 1px solid #eee;
        text-align: center;
        border-radius: 8px;
    }

    .payment-result_section .countDown {
        text-align: center;
        padding: 0;
        color: #777;
        margin-top: 5rem;
        font-size: 0.9rem;
    }
    
    .successful ,
    .Unsuccessful{
        text-align: center;
    }
    
    .successful > span {
        display: block;
        width: 64px;
        height: 64px;
        background: #00860030;
        border-radius: 50%;
        text-align: center;
        line-height: 3.75;
        margin: 2rem auto;
    }
    
    .successful > p {
        text-align: center;
        color: green;
        padding: 0;
        font-weight: 900;
    }
    
    .Unsuccessful > span {
        display: block;
        width: 64px;
        height: 64px;
        background: #ee5d5d30;
        border-radius: 50%;
        text-align: center;
        line-height: 3.75;
        margin: 0 auto;
    }
    .Unsuccessful > p {
        text-align: center;
        color: #df0101;
        padding: 1rem 0;
        font-weight: 900;
    }
    
    .btn {
      border: none;
      border-radius: 7px;
      cursor: pointer;
      color: #ffffff;
      font-family: "iranSans";
      letter-spacing: 0.05em;
      overflow: hidden;
      padding: 1.15em 3.5em;
      min-height: 3.3em;
      position: relative;
      text-transform: lowercase;
      margin-top: 0.5rem;
      font-size: 0.9rem;
      font-weight: 900;
    }
    
    .btn--green {
      background: green;
    }
    
    .btn--red {
      background: #df0101;
    }
    
    .btn:active, .btn:focus ,.btn:hover {
      outline: 3px solid hsl(calc(var(--hue) + 90), 98%, 80%);
      opacity:0.8;
    }
    .btn + .btn {
      margin-top: 2.5em;
    }
    .btn__txt {
      position: relative;
      z-index: 2;
    }
</style>

<body>
 
<div class="payment-result_section">
    @if($res['status']==0)
    <div class="successful">
        <span>
            <img src="https://dl.yadaksadra.com/web/successful-icon.png" alt="successful" width="32px">
        </span>
        
        <p class="text-success">پرداخت با موفقیت انجام شد.</p>
        
        <p class="countDown">
            بازگشت خودکار تا
            <span class="countDown" id="countDown">20</span>
            دیگر
        </p>
        
        <button type="button" class="btn btn--green" onclick="window.location = 'https://yadaksadra.com';">
        	<span class="btn__txt">بازگشت به یدک صدرا</
        </button>
    </div>
    @else
    
    
    <div class="Unsuccessful">
        <span>
            <img src="https://dl.yadaksadra.com/web/Unsuccessful-icon.png" alt="Unsuccessful" width="32px"/>
        </span>
        
        <p class="text-danger">{{$res['message']}}</p>
        
        <p class="countDown">
            بازگشت خودکار تا
            <span class="countDown" id="countDown">20</span>
            دیگر
        </p>
        
        <button type="button" class="btn btn--red" onclick="window.location = 'https://yadaksadra.com';">
        	<span class="btn__txt">بازگشت به یدک صدرا</
        </button>
    </div>
    @endif
               
</div>

<script>
    var count = 20;
        setInterval(function(){
            count--;
            document.getElementById('countDown').innerHTML = count;
            if (count == 0) {
                window.location = 'https://yadaksadra.com'; 
            }
        },1000);
</script>
</body>
</html>
