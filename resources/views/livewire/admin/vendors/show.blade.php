<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $vendor->business_name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $vendor->vendor_code }} · {{ $vendor->pmt_code }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.vendors.create', $vendor->id) }}"
               class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Edit Registration
            </a>
            <a href="{{ route('admin.vendors') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                ← Back to Vendors
            </a>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @php
            $statusCls = match($vendor->status) { 'active'=>'bg-emerald-50 text-emerald-700 border-emerald-200','suspended'=>'bg-red-50 text-red-600 border-red-200',default=>'bg-slate-50 text-slate-600 border-slate-200' };
            $kycCls    = match($vendor->kyc_status) { 'verified'=>'bg-emerald-50 text-emerald-700 border-emerald-200','rejected'=>'bg-red-50 text-red-600 border-red-200',default=>'bg-amber-50 text-amber-700 border-amber-200' };
        @endphp
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">Status</p>
            <span class="mt-2 inline-block rounded-full border px-3 py-1 text-sm font-bold {{ $statusCls }}">{{ ucfirst($vendor->status) }}</span>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">KYC</p>
            <span class="mt-2 inline-block rounded-full border px-3 py-1 text-sm font-bold {{ $kycCls }}">{{ ucfirst($vendor->kyc_status) }}</span>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">Registration Step</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $vendor->registration_step }} / 7</p>
        </div>
        <div class="fi-card p-4 text-center">
            <p class="text-xs text-slate-400">API Enabled</p>
            <p class="mt-2 text-sm font-bold {{ $vendor->api_enabled ? 'text-emerald-600' : 'text-slate-400' }}">{{ $vendor->api_enabled ? 'Yes' : 'No' }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    @php $tab = request()->query('tab', 'overview'); @endphp
    <div class="mb-5 flex gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
        @foreach(['overview'=>'Overview','legal'=>'Legal','promoters'=>'Promoters','directors'=>'Directors','business'=>'Business Plan','evaluation'=>'Evaluation'] as $t => $lbl)
            <a href="{{ route('admin.vendors.show', ['vendor'=>$vendor->id,'tab'=>$t]) }}"
               class="flex-1 rounded-lg py-2 text-center text-sm font-semibold transition
                      {{ $tab === $t ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $lbl }}
            </a>
        @endforeach
    </div>


    {{-- ── OVERVIEW ── --}}
    @if($tab === 'overview')
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="fi-card p-6">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Business Details</h3>
            <div class="space-y-3">
                @foreach([
                    ['Business Name',  $vendor->business_name],
                    ['Contact Person', $vendor->contact_name],
                    ['Email',          $vendor->email],
                    ['Phone',          $vendor->phone],
                    ['Country',        $vendor->country],
                    ['Address',        $vendor->address ?? '—'],
                    ['Vendor Code',    $vendor->vendor_code],
                    ['PMT Code',       $vendor->pmt_code ?? '—'],
                    ['Registered',     $vendor->created_at->format('d M Y')],
                ] as [$l,$v])
                    <div class="flex items-start justify-between gap-4">
                        <span class="shrink-0 text-xs text-slate-400">{{ $l }}</span>
                        <span class="text-right text-xs font-semibold text-slate-800">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="fi-card p-6">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Financial Settings</h3>
            <div class="space-y-3">
                @foreach([
                    ['Transaction Limit',  '₹'.number_format((float)$vendor->transaction_limit,2)],
                    ['Commission Type',    ucfirst($vendor->commission_type)],
                    ['Commission Value',   $vendor->commission_value.'%'],
                    ['API Enabled',        $vendor->api_enabled ? 'Yes' : 'No'],
                ] as [$l,$v])
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">{{ $l }}</span>
                        <span class="text-xs font-semibold text-slate-800">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif


    {{-- ── LEGAL ── --}}
    @if($tab === 'legal')
    <div class="fi-card p-6">
        <h3 class="mb-5 text-sm font-semibold text-slate-900">Legal Details</h3>
        @if($vendor->legalDetails)
            @php $ld = $vendor->legalDetails; @endphp
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @foreach([
                    ['Entity Type',              $ld->entity_type ?? '—'],
                    ['Registration Body',        $ld->registration_body ?? '—'],
                    ['Registration Number',      $ld->registration_number ?? '—'],
                    ['Tax Identification',       $ld->tax_identification ?? '—'],
                    ['RBI Regulated',            $ld->rbi_regulated ? 'Yes' : 'No'],
                    ['Incorporation Year',       $ld->incorporation_year ?? '—'],
                    ['Merchant Acquiring Years', $ld->merchant_acquiring_years ?? '—'],
                    ['Additional Licenses',      $ld->additional_licenses ?? '—'],
                ] as [$l,$v])
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-semibold text-slate-400">{{ $l }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-800">{{ $v }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400">No legal details submitted.</p>
        @endif
    </div>
    @endif


    {{-- ── PROMOTERS ── --}}
    @if($tab === 'promoters')
    <div class="fi-card overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-sm font-semibold text-slate-900">Promoters / Shareholders</h3>
        </div>
        @if($vendor->promoters->isNotEmpty())
            <div class="divide-y divide-slate-50">
                @foreach($vendor->promoters as $i => $p)
                    <div class="p-6">
                        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Promoter {{ $i+1 }}</p>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                            @foreach([['Name',$p->name],['Share %',$p->share_percentage],['PAN',$p->pan],['DOB',$p->dob?->format('d M Y') ?? '—'],['Address',$p->address]] as [$l,$v])
                                <div>
                                    <p class="text-xs font-semibold text-slate-400">{{ $l }}</p>
                                    <p class="mt-0.5 text-sm font-medium text-slate-800">{{ $v ?? '—' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-10 text-center text-sm text-slate-400">No promoters submitted.</div>
        @endif
    </div>
    @endif


    {{-- ── DIRECTORS ── --}}
    @if($tab === 'directors')
    <div class="space-y-5">
        <div class="fi-card overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-sm font-semibold text-slate-900">Directors / KMP</h3>
            </div>
            @if($vendor->directors->isNotEmpty())
                <div class="divide-y divide-slate-50">
                    @foreach($vendor->directors as $i => $d)
                        <div class="p-6">
                            <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Director {{ $i+1 }}</p>
                            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                                @foreach([['Name',$d->name],['Designation',$d->designation],['PAN',$d->pan_card_no],['DOB',$d->date_of_birth?->format('d M Y') ?? '—'],['Address',$d->official_address]] as [$l,$v])
                                    <div>
                                        <p class="text-xs font-semibold text-slate-400">{{ $l }}</p>
                                        <p class="mt-0.5 text-sm font-medium text-slate-800">{{ $v ?? '—' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-10 text-center text-sm text-slate-400">No directors submitted.</div>
            @endif
        </div>

        @if($vendor->teamItDetails)
        @php $ti = $vendor->teamItDetails; @endphp
        <div class="fi-card p-6">
            <h3 class="mb-5 text-sm font-semibold text-slate-900">Team & IT</h3>
            <div class="mb-5 grid grid-cols-2 gap-4 md:grid-cols-5">
                @foreach([['Total',$ti->total_employees],['Tech',$ti->technology_employees],['Sales',$ti->sales_employees],['Support',$ti->support_employees],['Admin/HR',$ti->admin_finance_hr_employees]] as [$l,$v])
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-3 text-center">
                        <p class="text-xl font-bold text-slate-900">{{ $v ?? 0 }}</p>
                        <p class="text-xs text-slate-400">{{ $l }}</p>
                    </div>
                @endforeach
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @foreach([['Processing Systems',$ti->processing_systems],['Applications',$ti->applications],['Database',$ti->database_system],['Switch',$ti->switch_system],['Terminals',$ti->terminals],['Fraud & Risk',$ti->fraud_risk_management]] as [$l,$v])
                    @if($v)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-semibold text-slate-400">{{ $l }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-800">{{ $v }}</p>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif


    {{-- ── BUSINESS PLAN ── --}}
    @if($tab === 'business')
    <div class="fi-card overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-sm font-semibold text-slate-900">Business Plan</h3>
        </div>
        @if($vendor->businessPlans->isNotEmpty())
            <div class="overflow-x-auto fi-scroll">
                <table class="min-w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach(['Month','Customer Registrations','Transactions','Total Volume'] as $col)
                                <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($vendor->businessPlans as $plan)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-800">{{ $plan->month }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-700">{{ number_format($plan->customer_registrations) }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-slate-700">{{ number_format($plan->transactions) }}</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-semibold text-slate-900">₹{{ number_format($plan->total_volume) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-10 text-center text-sm text-slate-400">No business plan submitted.</div>
        @endif
    </div>
    @endif


    {{-- ── EVALUATION ── --}}
    @if($tab === 'evaluation')
    <div class="fi-card p-6">
        <h3 class="mb-5 text-sm font-semibold text-slate-900">Evaluation & Compliance</h3>
        @if($vendor->evaluation)
            @php $ev = $vendor->evaluation; @endphp
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @foreach([
                    ['CA Name',              $ev->ca_name],
                    ['CA Constitution',      $ev->ca_constitution],
                    ['Incorporation Date',   $ev->ca_incorporation_date?->format('d M Y')],
                    ['Net Worth',            $ev->networth ? '₹'.number_format($ev->networth) : null],
                    ['Credit Rating',        $ev->credit_rating],
                    ['Bank Since',           $ev->dealing_with_bank_since],
                    ['Contract Expiry',      $ev->contract_expiry_date?->format('d M Y')],
                    ['Engagement Scope',     $ev->engagement_scope],
                    ['Documentation Status', $ev->documentation_status],
                    ['Conflict of Interest', $ev->conflict_of_interest],
                    ['RBI Defaulter',        $ev->rbi_defaulter],
                    ['Recommendations',      $ev->recommendations],
                ] as [$l,$v])
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-semibold text-slate-400">{{ $l }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-800">{{ $v ?? '—' }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400">No evaluation submitted.</p>
        @endif
    </div>
    @endif

</div>
