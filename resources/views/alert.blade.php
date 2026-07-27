<style>
    #guciravel-alert {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 450px;
        max-height: 80vh;
        overflow-y: auto;
        background: #fef2f2;
        border: 1px solid #ef4444;
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.3);
        z-index: 999999;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #7f1d1d;
    }
    #guciravel-alert .guciravel-header {
        background: #ef4444;
        color: white;
        padding: 12px 16px;
        font-weight: bold;
        font-size: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top-left-radius: 7px;
        border-top-right-radius: 7px;
    }
    #guciravel-alert .guciravel-close {
        cursor: pointer;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        border-radius: 4px;
        padding: 4px 8px;
    }
    #guciravel-alert .guciravel-close:hover {
        background: rgba(255,255,255,0.3);
    }
    #guciravel-alert .guciravel-body {
        padding: 16px;
    }
    .guciravel-item {
        background: white;
        border: 1px solid #fecaca;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 12px;
    }
    .guciravel-item:last-child {
        margin-bottom: 0;
    }
    .guciravel-badge {
        display: inline-block;
        background: #fee2e2;
        color: #b91c1c;
        font-size: 12px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 4px;
        margin-bottom: 8px;
    }
    .guciravel-sql {
        font-family: monospace;
        font-size: 12px;
        background: #f8fafc;
        padding: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
        margin-bottom: 8px;
        word-break: break-all;
    }
    .guciravel-source {
        font-size: 13px;
        color: #b91c1c;
        font-weight: 500;
    }
    .guciravel-remedy {
        margin-top: 8px;
        font-size: 13px;
        color: #047857;
        background: #ecfdf5;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #6ee7b7;
    }
</style>

<div id="guciravel-alert">
    <div class="guciravel-header">
        <span>♨️ Guciravel v{{ \Ginganomercy\Guciravel\HealerEngine::VERSION }}: N+1 Detected!</span>
        <button class="guciravel-close" onclick="document.getElementById('guciravel-alert').remove()">×</button>
    </div>
    <div class="guciravel-body">
        @foreach($queries as $q)
        <div class="guciravel-item">
            <span class="guciravel-badge">{{ $q['count'] }} Duplicate Queries</span>
            <div class="guciravel-sql">{{ $q['sql'] }}</div>
            <div class="guciravel-source">📍 {{ $q['source'] }}</div>
            <div class="guciravel-remedy">
                💊 <b>Saran Obat:</b> Gunakan metode Eager Loading <code>->with('relasi')</code> pada Eloquent Model Anda.
            </div>
        </div>
        @endforeach
    </div>
</div>
