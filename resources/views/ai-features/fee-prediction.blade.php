@extends('layouts.app')
@section('title', 'AI Fee Predictor')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-cash-coin text-warning"></i> AI Fee Predictor</h2>
            <p class="text-muted mb-0">Predict which students are likely to default on fee payments based on history.</p>
        </div>
        <a href="{{ route('ai_features.web.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="row">
        <div class="col-12 col-lg-5 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form id="aiForm">
                        <div class="mb-3">
                            <label class="form-label font-bold">Fee History Data (JSON)</label>
                            <textarea id="dataInput" class="form-control text-monospace" rows="12" required>[
  {"term": "Term 1", "amount": 500, "paid": 500, "days_late": 0},
  {"term": "Term 2", "amount": 500, "paid": 250, "days_late": 15},
  {"term": "Term 3", "amount": 500, "paid": 0, "days_late": 45}
]</textarea>
                            <small class="text-muted">In production, this would be fetched automatically from the database.</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="generateBtn">
                            <i class="bi bi-magic"></i> Predict Default Risk
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="font-bold mb-0">AI Prediction Insights</h5>
                </div>
                <div class="card-body p-4 position-relative">
                    <div id="loadingState" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                        <p class="mt-3 text-muted">Analyzing financial history...</p>
                    </div>
                    <div id="resultOutput" class="markdown-body p-3 bg-slate-50 rounded" style="min-height: 200px; white-space: pre-wrap; display: none;"></div>
                    <div id="emptyState" class="text-center py-5 text-muted">
                        <i class="bi bi-wallet2 display-4 text-gray-300"></i>
                        <p class="mt-3">The risk prediction will appear here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('aiForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    let data;
    try {
        data = JSON.parse(document.getElementById('dataInput').value);
    } catch(err) {
        alert("Invalid JSON format in the input box.");
        return;
    }
    
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('resultOutput').style.display = 'none';
    document.getElementById('loadingState').style.display = 'block';

    try {
        const response = await fetch('{{ route("api.v1.ai_features.fee") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ fee_history: data })
        });
        const resData = await response.json();
        if(response.ok) {
            document.getElementById('resultOutput').textContent = resData.response || resData.data; 
            document.getElementById('resultOutput').style.display = 'block';
        } else {
            alert('Error: ' + (resData.message || 'Unknown error'));
        }
    } catch(err) {
        alert('Failed to connect to AI Service.');
    } finally {
        document.getElementById('loadingState').style.display = 'none';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic"></i> Predict Default Risk';
    }
});
</script>
@endsection
