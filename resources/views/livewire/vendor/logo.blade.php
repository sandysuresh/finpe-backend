<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Upload Logo</h1>
        <p class="mt-1 text-sm text-slate-500">Profile &amp; KYC. This logo appears in your vendor panel header.</p>
    </div>

    @if($successMsg)
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ $successMsg }}</div>
    @endif

    <div class="fi-card max-w-xl p-6">
        <p class="text-sm font-semibold text-slate-900">Current logo</p>
        <div class="mt-4 flex h-24 items-center">
            @if($currentLogo)
                <img src="{{ asset('storage/'.$currentLogo) }}" alt="Uploaded logo" class="max-h-20 max-w-full object-contain">
            @else
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3 20 7.5v9L12 21l-8-4.5v-9L12 3Z"/>
                            <path d="m8 10 4-2 4 2-4 2-4-2Zm0 4 4 2 4-2"/>
                        </svg>
                    </div>
                    <span class="text-sm text-slate-600">Default FinPe logo</span>
                </div>
            @endif
        </div>

        <form wire:submit="save" class="mt-6 space-y-4">
            <label class="block text-sm font-medium text-slate-700">
                Company logo
                <input wire:model="logo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full text-sm text-slate-600">
            </label>
            @error('logo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="text-xs text-slate-500">PNG, JPG, JPEG, or WebP. Maximum 2 MB. Between 32 and 4000 pixels on each side.</p>

            @if($logo)
                <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview" class="max-h-20 max-w-full object-contain">
            @endif

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-violet-700 px-4 py-2.5 text-sm font-semibold text-white">Save logo</button>
                @if($currentLogo)
                    <button type="button" wire:click="remove" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700">Remove logo</button>
                @endif
            </div>
        </form>
    </div>
</div>
