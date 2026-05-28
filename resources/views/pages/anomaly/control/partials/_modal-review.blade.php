<div id="reviewModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     onclick="if(event.target===this) closeReviewModal()">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    {{-- Modal --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg">

        {{-- Header --}}
        <div class="flex items-start justify-between p-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-gavel text-sky-600"></i>
                    <h3 class="font-semibold text-slate-800" id="modal-title">Review Anomali</h3>
                </div>
                <div id="modal-severity"></div>
            </div>
            <button onclick="closeReviewModal()"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-5 space-y-4">
            <input type="hidden" id="modal-anomaly-id">

            {{-- Meta Data --}}
            <div id="modal-meta" class="bg-slate-50 rounded-xl p-4"></div>

            {{-- Keputusan --}}
            <div>
                <label class="text-sm font-medium text-slate-700 mb-2 block">Keputusan</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach([
                        ['value' => 'approved', 'label' => 'Approve', 'color' => 'emerald', 'icon' => 'fa-check'],
                        ['value' => 'approved_with_note', 'label' => 'Approve + Catatan', 'color' => 'yellow', 'icon' => 'fa-note-sticky'],
                        ['value' => 'rejected', 'label' => 'Reject', 'color' => 'red', 'icon' => 'fa-xmark'],
                    ] as $opt)
                    <label class="cursor-pointer">
                        <input type="radio" name="modal_decision_radio" value="{{ $opt['value'] }}"
                               class="peer sr-only"
                               onchange="document.getElementById('modal-decision').value=this.value">
                        <div class="border-2 border-slate-200 rounded-xl p-3 text-center text-xs font-medium text-slate-500
                                    peer-checked:border-{{ $opt['color'] }}-500 peer-checked:bg-{{ $opt['color'] }}-50
                                    peer-checked:text-{{ $opt['color'] }}-700 transition hover:bg-slate-50">
                            <i class="fa-solid {{ $opt['icon'] }} block text-base mb-1"></i>
                            {{ $opt['label'] }}
                        </div>
                    </label>
                    @endforeach
                </div>
                <input type="hidden" id="modal-decision">
            </div>

            {{-- Justifikasi --}}
            <div>
                <label for="modal-justification" class="text-sm font-medium text-slate-700 mb-1.5 block">
                    Justifikasi <span class="text-red-500">*</span>
                </label>
                <textarea id="modal-justification" rows="3"
                          placeholder="Tuliskan alasan keputusan Anda secara detail..."
                          class="w-full text-sm border border-slate-200 rounded-xl px-4 py-2.5 resize-none
                                 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none"></textarea>
                <p class="text-xs text-slate-400 mt-1">Wajib diisi. Akan tersimpan di log keputusan.</p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 border-t border-slate-100 flex justify-end gap-2">
            <button onclick="closeReviewModal()"
                    class="px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                Batal
            </button>
            <button onclick="submitReview()"
                    class="px-5 py-2 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-lg transition inline-flex items-center gap-2">
                <i class="fa-solid fa-gavel text-xs"></i> Simpan Keputusan
            </button>
        </div>

    </div>
</div>