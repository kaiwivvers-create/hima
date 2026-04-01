@extends('dashboard.layout')

@section('title', 'Payment Proofs')
@section('page_title', 'Payment Proofs')

@section('content')
<section class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Student</th>
                <th>Parent</th>
                <th>Proof</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($proofs as $proof)
                <tr>
                    <td>{{ $proof->payment?->invoice_no ?? '-' }}</td>
                    <td>{{ $proof->payment?->student?->name ?? '-' }}</td>
                    <td>{{ $proof->parent?->name ?? '-' }}</td>
                    <td>
                        <button type="button" class="btn-outline" data-modal-open="proof-image-{{ $proof->id }}">
                            <img
                                src="{{ asset('storage/'.$proof->image_path) }}"
                                alt="Payment Proof"
                                style="width:90px;height:90px;object-fit:cover;border-radius:10px;border:1px solid var(--line);background:#fff7d1;"
                            >
                        </button>
                        <div style="margin-top:.35rem;">
                            <button type="button" class="btn-outline" data-modal-open="proof-image-{{ $proof->id }}">{{ __('View Image') }}</button>
                        </div>
                        @if ($proof->note)
                            <div class="muted" style="margin-top:.35rem;">{{ $proof->note }}</div>
                        @endif
                    </td>
                    <td>{{ ucfirst($proof->status) }}</td>
                    <td>{{ $proof->created_at?->format('Y-m-d H:i') }}</td>
                    <td>
                        @if ($proof->status === 'pending')
                            <div class="actions">
                                <form method="POST" action="{{ route('dashboard.payments.proofs.approve', ['proof' => $proof, 'lang' => app()->getLocale()]) }}">
                                    @csrf
                                    <button type="submit" class="btn">Approve & Mark Paid</button>
                                </form>
                                <form method="POST" action="{{ route('dashboard.payments.proofs.reject', ['proof' => $proof, 'lang' => app()->getLocale()]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </form>
                            </div>
                        @else
                            <span class="muted">
                                {{ $proof->reviewer?->name ? 'By '.$proof->reviewer->name : '' }}
                                {{ $proof->reviewed_at ? 'on '.$proof->reviewed_at->format('Y-m-d H:i') : '' }}
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="muted">No payment proofs submitted yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">{{ $proofs->withQueryString()->links() }}</div>
</section>

@foreach ($proofs as $proof)
    <div class="modal" id="proof-image-{{ $proof->id }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card" style="width:min(960px, 100%);">
            <div class="modal-head">
                <h2>{{ __('Proof Image') }}</h2>
                <button class="btn-outline" type="button" data-modal-close>{{ __('Close') }}</button>
            </div>
            <img
                src="{{ asset('storage/'.$proof->image_path) }}"
                alt="Payment Proof"
                style="width:100%;max-height:75vh;object-fit:contain;border-radius:10px;border:1px solid var(--line);background:#fff7d1;"
            >
            @if ($proof->note)
                <p class="muted" style="margin:.7rem 0 0;">{{ $proof->note }}</p>
            @endif
        </div>
    </div>
@endforeach
@endsection
