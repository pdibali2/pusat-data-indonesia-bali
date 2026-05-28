@php
$map = [
    'warning'           => ['bg-amber-100 text-amber-700',   'fa-triangle-exclamation','Warning'],
    'under_review'      => ['bg-indigo-100 text-indigo-700', 'fa-magnifying-glass',    'Under Review'],
    'approved'          => ['bg-emerald-100 text-emerald-700','fa-check-circle',        'Approved'],
    'approved_with_note'=> ['bg-teal-100 text-teal-700',     'fa-note-sticky',         'Approved+Note'],
    'rejected'          => ['bg-red-100 text-red-700',       'fa-ban',                 'Rejected'],
    'resolved'          => ['bg-slate-100 text-slate-500',   'fa-check',               'Resolved'],
];
[$cls, $icon, $label] = $map[$status] ?? ['bg-slate-100 text-slate-400','fa-question','Unknown'];
@endphp
<span class="inline-flex items-center gap-1 {{ $cls }} text-xs font-medium px-2.5 py-1 rounded-full">
    <i class="fa-solid {{ $icon }} text-[10px]"></i> {{ $label }}
</span>