<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="noindex, nofollow">
  <title>Laravel log viewer</title>
  <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
        crossorigin="anonymous">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css">
  <style>
    body {
      padding: 25px;
    }

    h1 {
      font-size: 1.5em;
      margin-top: 0;
    }

    .d-rtl {
      direction: rtl
    }

    #table-log {
        font-size: 0.85rem;
    }

    .sidebar {
        font-size: 0.85rem;
        line-height: 1;
    }

    .btn {
        font-size: 0.7rem;
    }

    .stack {
      font-size: 0.85em;
    }

    .date {
      min-width: 75px;
    }

    .text {
      word-break: break-all;
    }

    a.llv-active {
      z-index: 2;
      background-color: #f5f5f5;
      border-color: #777;
    }

    .list-group-item {
      word-break: break-word;
    }

    .folder {
      padding-top: 15px;
    }

    .div-scroll {
      height: 80vh;
      overflow: hidden auto;
    }
    .nowrap {
      white-space: nowrap;
    }
    .list-group {
        padding: 5px;
    }


    #table-log_filter input {
      text-align: right
    }

    /**
    * DARK MODE CSS
    */

    body[data-theme="dark"] {
      background-color: #151515;
      color: #cccccc;
    }

    [data-theme="dark"] a {
      color: #4da3ff;
    }

    [data-theme="dark"] a:hover {
      color: #a8d2ff;
    }

    [data-theme="dark"] .list-group-item {
      background-color: #1d1d1d;
      border-color: #444;
    }

    [data-theme="dark"] a.llv-active {
        background-color: #0468d2;
        border-color: rgba(255, 255, 255, 0.125);
        color: #ffffff;
    }

    [data-theme="dark"] a.list-group-item:focus, [data-theme="dark"] a.list-group-item:hover {
      background-color: #273a4e;
      border-color: rgba(255, 255, 255, 0.125);
      color: #ffffff;
    }

    [data-theme="dark"] .table td, [data-theme="dark"] .table th,[data-theme="dark"] .table thead th {
      border-color:#616161;
    }

    [data-theme="dark"] .page-item.disabled .page-link {
      color: #8a8a8a;
      background-color: #151515;
      border-color: #5a5a5a;
    }

    [data-theme="dark"] .page-link {
      background-color: #151515;
      border-color: #5a5a5a;
    }

    [data-theme="dark"] .page-item.active .page-link {
      color: #fff;
      background-color: #0568d2;
      border-color: #007bff;
    }

    [data-theme="dark"] .page-link:hover {
      color: #ffffff;
      background-color: #0051a9;
      border-color: #0568d2;
    }

    [data-theme="dark"] .form-control {
      border: 1px solid #464646;
      background-color: #151515;
      color: #bfbfbf;
    }

    [data-theme="dark"] .form-control:focus {
      color: #bfbfbf;
      background-color: #212121;
      border-color: #4a4a4a;
  }

  </style>

  <script>
    function initTheme() {
      const darkThemeSelected =
        localStorage.getItem('darkSwitch') !== null &&
        localStorage.getItem('darkSwitch') === 'dark';
      darkSwitch.checked = darkThemeSelected;
      darkThemeSelected ? document.body.setAttribute('data-theme', 'dark') :
        document.body.removeAttribute('data-theme');
    }

    function resetTheme() {
      if (darkSwitch.checked) {
        document.body.setAttribute('data-theme', 'dark');
        localStorage.setItem('darkSwitch', 'dark');
      } else {
        document.body.removeAttribute('data-theme');
        localStorage.removeItem('darkSwitch');
      }
    }
  </script>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-10 table-container border rounded py-4">
      @if ($logs === null)
        <div>
          Log file >50M, please download it.
        </div>
      @else
        <table id="table-log" class="table table-striped text-right d-rtl" data-ordering-index="{{ $standardFormat ? 2 : 0 }}">
          <thead>
          <tr>
            @if ($standardFormat)
              <th>سطح</th>
              <th>متن نوشته</th>
              <th>تاریخ و ساعت</th>
            @else
              <th>شماره خط</th>
            @endif
            <th>متن</th>
          </tr>
          </thead>
          <tbody>

          @foreach($logs as $key => $log)
            <tr data-display="stack{{{$key}}}">
              @if ($standardFormat)
                <td class="nowrap text-{{{$log['level_class']}}}">
                  <span class="fa fa-{{{$log['level_img']}}}" aria-hidden="true"></span>&nbsp;&nbsp;{{$log['level']}}
                </td>
                <td class="text">{{$log['context']}}</td>
              @endif
              <td class="date">{{{$log['date']}}}</td>
              <td class="text">
                @if ($log['stack'])
                  <button type="button"
                          class="float-right expand btn btn-outline-dark btn-sm mb-2 ml-2"
                          data-display="stack{{{$key}}}">
                    <span class="fa fa-search"></span>
                  </button>
                @endif
                {{{$log['text']}}}
                @if (isset($log['in_file']))
                  <br/>{{{$log['in_file']}}}
                @endif
                @if ($log['stack'])
                  <div class="stack" id="stack{{{$key}}}"
                       style="display: none; white-space: pre-wrap;">{{{ trim($log['stack']) }}}
                  </div>
                @endif
              </td>
            </tr>
          @endforeach

          </tbody>
        </table>
      @endif
      <div class="p-3 d-rtl text-right">
        @if($current_file)
          <a href="?dl={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-download"></span> دانلود
          </a>
          
          <a class="mr-2" id="clean-log" href="?clean={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-sync"></span> خالی کردن
          </a>
          
          <a class="mr-2" id="delete-log" href="?del={{ \Illuminate\Support\Facades\Crypt::encrypt($current_file) }}{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
            <span class="fa fa-trash"></span> حذف
          </a>
          @if(count($files) > 1)
            
            <a class="mr-2" id="delete-all-log" href="?delall=true{{ ($current_folder) ? '&f=' . \Illuminate\Support\Facades\Crypt::encrypt($current_folder) : '' }}">
              <span class="fa fa-trash-alt"></span> حذف همه
            </a>
          @endif
        @endif
      </div>
    </div>
    
    <div class="col sidebar mb-3 text-right">
      <div class="list-group div-scroll">
        @foreach($folders as $folder)
          <div class="list-group-item">
            <?php
            \Rap2hpoutre\LaravelLogViewer\LaravelLogViewer::DirectoryTreeStructure( $storage_path, $structure );
            ?>

          </div>
        @endforeach
        @foreach($files as $file)
          <a href="?l={{ \Illuminate\Support\Facades\Crypt::encrypt($file) }}"
             class="list-group-item @if ($current_file == $file) llv-active @endif">
            {{$file}}
          </a>
        @endforeach
      </div>
    </div>
  </div>
</div>
<!-- jQuery for Bootstrap -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
<!-- FontAwesome -->
<script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
<!-- Datatables -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>

<script>
  $(document).ready(function () {
    $('.table-container tr').on('click', function () {
      $('#' + $(this).data('display')).toggle();
    });

    $('#table-log').DataTable({
      "order": [$('#table-log').data('orderingIndex'), 'desc'],
      "stateSave": true,
      "stateSaveCallback": function (settings, data) {
        window.localStorage.setItem("datatable", JSON.stringify(data));
      },
      "stateLoadCallback": function (settings) {
        var data = JSON.parse(window.localStorage.getItem("datatable"));
        if (data) data.start = 0;
        return data;
      },
      language: {
          "searchPlaceholder": 'جستجو',
          "search": '',
          "emptyTable": "هیچ داده‌ای در جدول وجود ندارد",
          "info": "نمایش _START_ تا _END_ از _TOTAL_ ردیف",
          "infoEmpty": "نمایش 0 تا 0 از 0 ردیف",
          "infoFiltered": "(فیلتر شده از _MAX_ ردیف)",
          "infoThousands": ",",
          "lengthMenu": "نمایش _MENU_ ردیف",
          "processing": "در حال پردازش...",
          "zeroRecords": "رکوردی با این مشخصات پیدا نشد",
          "paginate": {
              "next": "بعدی",
              "previous": "قبلی",
              "first": "ابتدا",
              "last": "انتها"
          },
          "aria": {
              "sortAscending": ": فعال سازی نمایش به صورت صعودی",
              "sortDescending": ": فعال سازی نمایش به صورت نزولی"
          },
          "autoFill": {
              "cancel": "انصراف",
              "fill": "پر کردن همه سلول ها با ساختار سیستم",
              "fillHorizontal": "پر کردن سلول به صورت افقی",
              "fillVertical": "پرکردن سلول به صورت عمودی"
          },
          "buttons": {
              "collection": "مجموعه",
              "colvis": "قابلیت نمایش ستون",
              "colvisRestore": "بازنشانی قابلیت نمایش",
              "copy": "کپی",
              "copySuccess": {
                  "1": "یک ردیف داخل حافظه کپی شد",
                  "_": "%ds ردیف داخل حافظه کپی شد"
              },
              "copyTitle": "کپی در حافظه",
              "pageLength": {
                  "-1": "نمایش همه ردیف‌ها",
                  "_": "نمایش %d ردیف",
                  "1": "نمایش 1 ردیف"
              },
              "print": "چاپ",
              "copyKeys": "برای کپی داده جدول در حافظه سیستم کلید های ctrl یا ⌘ + C را فشار دهید",
              "csv": "فایل CSV",
              "pdf": "فایل PDF",
              "renameState": "تغییر نام",
              "updateState": "به روز رسانی",
              "excel": "فایل اکسل",
              "createState": "ایجاد وضعیت جدول",
              "removeAllStates": "حذف همه وضعیت ها",
              "removeState": "حذف",
              "savedStates": "وضعیت های ذخیره شده",
              "stateRestore": "بازگشت به وضعیت %d"
          },
          "searchBuilder": {
              "add": "افزودن شرط",
              "button": {
                  "0": "جستجو ساز",
                  "_": "جستجوساز (%d)"
              },
              "clearAll": "خالی کردن همه",
              "condition": "شرط",
              "conditions": {
                  "date": {
                      "after": "بعد از",
                      "before": "بعد از",
                      "between": "میان",
                      "empty": "خالی",
                      "not": "نباشد",
                      "notBetween": "میان نباشد",
                      "notEmpty": "خالی نباشد",
                      "equals": "برابر باشد با"
                  },
                  "number": {
                      "between": "میان",
                      "empty": "خالی",
                      "gt": "بزرگتر از",
                      "gte": "برابر یا بزرگتر از",
                      "lt": "کمتر از",
                      "lte": "برابر یا کمتر از",
                      "not": "نباشد",
                      "notBetween": "میان نباشد",
                      "notEmpty": "خالی نباشد",
                      "equals": "برابر باشد با"
                  },
                  "string": {
                      "contains": "حاوی",
                      "empty": "خالی",
                      "endsWith": "به پایان می رسد با",
                      "not": "نباشد",
                      "notEmpty": "خالی نباشد",
                      "startsWith": "شروع  شود با",
                      "notContains": "نباشد حاوی",
                      "notEndsWith": "پایان نیابد با",
                      "notStartsWith": "شروع نشود با",
                      "equals": "برابر باشد با"
                  },
                  "array": {
                      "empty": "خالی",
                      "contains": "حاوی",
                      "not": "نباشد",
                      "notEmpty": "خالی نباشد",
                      "without": "بدون",
                      "equals": "برابر باشد با"
                  }
              },
              "data": "اطلاعات",
              "logicAnd": "و",
              "logicOr": "یا",
              "title": {
                  "0": "جستجو ساز",
                  "_": "جستجوساز (%d)"
              },
              "value": "مقدار",
              "deleteTitle": "حذف شرط فیلتر",
              "leftTitle": "شرط بیرونی",
              "rightTitle": "شرط فرورفتگی"
          },
          "select": {
              "cells": {
                  "1": "1 سلول انتخاب شد",
                  "_": "%d سلول انتخاب شد"
              },
              "columns": {
                  "1": "یک ستون انتخاب شد",
                  "_": "%d ستون انتخاب شد"
              },
              "rows": {
                  "1": "1ردیف انتخاب شد",
                  "_": "%d  انتخاب شد"
              }
          },
          "thousands": ",",
          "searchPanes": {
              "clearMessage": "همه را پاک کن",
              "collapse": {
                  "0": "صفحه جستجو",
                  "_": "صفحه جستجو (٪ d)"
              },
              "count": "{total}",
              "countFiltered": "{shown} ({total})",
              "emptyPanes": "صفحه جستجو وجود ندارد",
              "loadMessage": "در حال بارگیری صفحات جستجو ...",
              "title": "فیلترهای فعال - %d",
              "showMessage": "نمایش همه",
              "collapseMessage": "بستن همه"
          },
          "loadingRecords": "در حال بارگذاری...",
          "datetime": {
              "previous": "قبلی",
              "next": "بعدی",
              "hours": "ساعت",
              "minutes": "دقیقه",
              "seconds": "ثانیه",
              "amPm": [
                  "صبح",
                  "عصر"
              ],
              "months": {
                  "0": "ژانویه",
                  "1": "فوریه",
                  "10": "نوامبر",
                  "4": "می",
                  "8": "سپتامبر",
                  "11": "دسامبر",
                  "3": "آوریل",
                  "9": "اکتبر",
                  "7": "اوت",
                  "2": "مارس",
                  "5": "ژوئن",
                  "6": "ژوئیه"
              },
              "unknown": "-",
              "weekdays": [
                  "یکشنبه",
                  "دوشنبه",
                  "سه‌شنبه",
                  "چهارشنبه",
                  "پنجشنبه",
                  "جمعه",
                  "شنبه"
              ]
          },
          "editor": {
              "close": "بستن",
              "create": {
                  "button": "جدید",
                  "title": "ثبت جدید",
                  "submit": "ایجــاد"
              },
              "edit": {
                  "button": "ویرایش",
                  "title": "ویرایش",
                  "submit": "به روز رسانی"
              },
              "remove": {
                  "button": "حذف",
                  "title": "حذف",
                  "submit": "حذف",
                  "confirm": {
                      "_": "آیا از حذف %d خط اطمینان دارید؟",
                      "1": "آیا از حذف یک خط اطمینان دارید؟"
                  }
              },
              "multi": {
                  "restore": "واگرد",
                  "noMulti": "این ورودی را می توان به صورت جداگانه ویرایش کرد، اما نه بخشی از یک گروه",
                  "title": "مقادیر متعدد",
                  "info": "مقادیر متعدد"
              },
              "error": {
                  "system": "خطایی رخ داده (اطلاعات بیشتر)"
              }
          },
          "decimal": ".",
          "stateRestore": {
              "creationModal": {
                  "button": "ایجاد",
                  "columns": {
                      "search": "جستجوی ستون",
                      "visible": "وضعیت نمایش ستون"
                  },
                  "name": "نام:",
                  "order": "مرتب سازی",
                  "paging": "صفحه بندی",
                  "search": "جستجو",
                  "select": "انتخاب",
                  "title": "ایجاد وضعیت جدید",
                  "toggleLabel": "شامل:",
                  "scroller": "موقعیت جدول (اسکرول)",
                  "searchBuilder": "صفحه جستجو"
              },
              "emptyError": "نام نمیتواند خالی باشد.",
              "removeConfirm": "آیا از حذف %s مطمئنید؟",
              "removeJoiner": "و",
              "renameButton": "تغییر نام",
              "renameLabel": "نام جدید برای $s :",
              "duplicateError": "وضعیتی با این نام از پیش ذخیره شده.",
              "emptyStates": "هیچ وضعیتی ذخیره نشده",
              "removeError": "حذف با خطا موماجه شد",
              "removeSubmit": "حذف وضعیت",
              "removeTitle": "حذف وضعیت جدول",
              "renameTitle": "تغییر نام وضعیت"
          }
      },
    });
    $('#delete-log, #clean-log, #delete-all-log').click(function () {
      return confirm('آیا مطمئنید?');
    });
  });
</script>
</body>
</html>
