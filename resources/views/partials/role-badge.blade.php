@auth
    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ auth()->user()->isAdmin() ? 'bg-[#0f2a4a] text-white' : 'bg-slate-100 text-[#0f2a4a]' }}" style="white-space:nowrap">
        {{ auth()->user()->roleLabel() }}
    </span>
@endauth
