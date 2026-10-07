<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير المريض - {{ $patient->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
            color: #1f2937;
            font-size: 12px;
            margin: 0;
        }
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #2563eb;
        }
        .header .meta {
            margin-top: 6px;
            color: #6b7280;
            font-size: 11px;
        }
        h2 {
            font-size: 14px;
            color: #2563eb;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin: 20px 0 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .info td {
            border: 1px solid #e5e7eb;
            padding: 7px 9px;
        }
        .info td.label {
            background: #f3f4f6;
            font-weight: bold;
            width: 22%;
        }
        .data th {
            background: #2563eb;
            color: #fff;
            border: 1px solid #2563eb;
            padding: 7px 9px;
            text-align: right;
            font-size: 11px;
        }
        .data td {
            border: 1px solid #e5e7eb;
            padding: 6px 9px;
        }
        .data tr:nth-child(even) td { background: #f9fafb; }
        .chip {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            margin: 0 0 4px;
        }
        .chip.red { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
        .chip.green { background: #dcfce7; border-color: #86efac; color: #166534; }
        .empty { color: #9ca3af; font-style: italic; }
        .note {
            border: 1px solid #e5e7eb;
            border-right: 3px solid #2563eb;
            background: #f9fafb;
            padding: 7px 10px;
            margin-bottom: 7px;
        }
        .note .date { color: #6b7280; font-size: 10px; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 10px;
            background: #e5e7eb;
        }
        .badge.done { background: #dcfce7; color: #166534; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .badge.active { background: #dbeafe; color: #1e40af; }
        .badge.queued { background: #fef9c3; color: #854d0e; }
        .badge.pending { background: #f3f4f6; color: #374151; }
        .avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            border: 2px solid #2563eb;
        }
        .with-avatar { display: table; width: 100%; }
        .with-avatar .col { display: table-cell; vertical-align: top; }
        .with-avatar .col.avatar-col { width: 90px; padding-left: 15px; }
        .footer {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>تقرير بيانات المريض</h1>
    <div class="meta">
        الطبيب: {{ $doctor->user->name ?? '—' }}
        @if($doctor->clinic) &nbsp;|&nbsp; العيادة: {{ $doctor->clinic->name }} @endif
        &nbsp;|&nbsp; تاريخ الإصدار: {{ \App\Support\ArabicDateFormatter::format(\Carbon\Carbon::now()) }}
    </div>
</div>

<h2>البيانات الشخصية</h2>
<div class="with-avatar">
    <div class="col avatar-col">
        @if($avatar)
            <img src="{{ $avatar }}" class="avatar">
        @endif
    </div>
    <div class="col">
        <table class="info">
            <tr>
                <td class="label">الاسم</td>
                <td>{{ $patient->name ?? '—' }}</td>
                <td class="label">رقم الهاتف</td>
                <td>{{ $patient->phone ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">البريد الإلكتروني</td>
                <td>{{ $patient->email ?? '—' }}</td>
                <td class="label">العمر</td>
                <td>{{ $patient->age ? \App\Support\ArabicDateFormatter::toArabicDigits($patient->age) : '—' }}</td>
            </tr>
            <tr>
                <td class="label">النوع</td>
                <td>{{ $patient->user?->gender?->value === 'Male' ? 'ذكر' : ($patient->user?->gender?->value === 'Female' ? 'أنثى' : '—') }}</td>
                <td class="label">المنطقة</td>
                <td>{{ $patient->area ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">العنوان</td>
                <td colspan="3">{{ $patient->user?->address ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">حالة الحظر</td>
                <td colspan="3">
                    @if($patient->is_blocked)
                        <span class="chip red">محظور</span>
                        @if($patient->block_reason)
                            &nbsp; السبب: {{ $patient->block_reason }}
                        @endif
                        @if($patient->blocked_at)
                            &nbsp;|&nbsp; منذ: {{ \App\Support\ArabicDateFormatter::format(\Carbon\Carbon::parse($patient->blocked_at), false) }}
                        @endif
                        @if($patient->expires_at)
                            &nbsp;|&nbsp; ينتهي: {{ \App\Support\ArabicDateFormatter::format(\Carbon\Carbon::parse($patient->expires_at), false) }}
                        @endif
                    @else
                        <span class="chip green">غير محظور</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<h2>المواعيد</h2>
<table class="data">
    <thead>
        <tr>
            <th>#</th>
            <th>التاريخ والوقت</th>
            <th>الحالة</th>
            <th>التقييم</th>
            <th>المدة (دقيقة)</th>
            <th>التأخير (دقيقة)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($patient->appointments as $appointment)
            <tr>
                <td>{{ \App\Support\ArabicDateFormatter::toArabicDigits($loop->iteration) }}</td>
                <td>
                    @php
                        $date = \Carbon\Carbon::parse($appointment->date);
                        $dateText = \App\Support\ArabicDateFormatter::format($date)
                            . ' ' . \App\Support\ArabicDateFormatter::toArabicDigits($date->year);
                    @endphp
                    {{ $dateText }}
                </td>
                <td>
                    @php
                        $statusLabels = [
                            'Active'   => ['نشط', 'active'],
                            'Done'     => ['تم', 'done'],
                            'Cancelled'=> ['ملغي', 'cancelled'],
                            'Queued'   => ['قائمة الانتظار', 'queued'],
                            'Pending'  => ['معلق', 'pending'],
                        ];
                        [$statusLabel, $statusClass] = $statusLabels[$appointment->status->value] ?? [$appointment->status->value, ''];
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td>
                    @php
                        $gradeLabels = [
                            'Done in Time'   => 'في الوقت المحدد',
                            'Doctor Delay'   => 'تأخير الطبيب',
                            'Patient Delay'  => 'تأخير المريض',
                            'Doctor Cancel'  => 'إلغاء الطبيب',
                            'Patient Cancel' => 'إلغاء المريض',
                        ];
                    @endphp
                    {{ $appointment->grade ? ($gradeLabels[$appointment->grade->value] ?? $appointment->grade->value) : '—' }}
                </td>
                <td>{{ \App\Support\ArabicDateFormatter::toArabicDigits($appointment->duration ?? 0) }}</td>
                <td>{{ \App\Support\ArabicDateFormatter::toArabicDigits($appointment->delay ?? 0) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty">لا توجد مواعيد مسجلة</td>
            </tr>
        @endforelse
    </tbody>
</table>

<h2>الملاحظات</h2>
@forelse($patient->notes as $note)
    <div class="note">
        <div>{{ $note->text }}</div>
        <div class="date">{{ \App\Support\ArabicDateFormatter::format($note->created_at) }}</div>
    </div>
@empty
    <div class="empty">لا توجد ملاحظات</div>
@endforelse

<h2>العلامات (Flags)</h2>
@forelse($patient->flags as $flag)
    <span class="chip">{{ $flag->name }}</span>
@empty
    <div class="empty">لا توجد علامات</div>
@endforelse

<div class="footer">
    تم إنشاء هذا التقرير بتاريخ {{ \App\Support\ArabicDateFormatter::format(\Carbon\Carbon::now()) }}
</div>

</body>
</html>
