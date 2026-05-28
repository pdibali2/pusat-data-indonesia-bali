@php
$map = [
    'critical' => ['bg-red-100 text-red-700',    'fa-circle-exclamation', 'Critical'],
    'high'     => ['bg-orange-100 text-orange-700','fa-triangle-exclamation','High'],
    'medium'   => ['bg-yellow-100 text-yellow-700','fa-circle-minus',       'Medium'],
    'low'      => ['bg-blue-100 text-blue-700',   'fa-circle-info',        'Low'],
];
[$cls, $icon, $label] = $map[$severity] ?? ['bg-slate-100 text-slate-500','fa-question','Unknown'];
@endphp
<span class="inline-flex items-center gap-1 {{ $cls }} text-xs font-semibold px-2.5 py-1 rounded-full">
    <i class="fa-solid {{ $icon }} text-[10px]"></i> {{ $label }}
</span>