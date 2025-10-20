@extends('layouts.app')

@section('title', 'Test API Credential')

@section('content')
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Test API Credential</h2>
        <p class="text-sm text-gray-600 mt-1">Test your API credentials with live requests</p>
      </div>
      <x-button variant="secondary" :href="route('api-credentials.show', $apiCredential)">
        Back to Details
      </x-button>
    </div>

    <!-- API Credentials Display -->
    <x-card title="API Credentials">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
          <code id="api-key"
            class="block text-xs bg-gray-900 text-green-400 p-3 rounded font-mono break-all">{{ $apiCredential->api_key }}</code>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">API Secret</label>
          <p class="text-xs text-red-600">⚠️ Secret is hidden. Please use your saved secret.</p>
        </div>
      </div>
    </x-card>

    <!-- Test Form -->
    <x-card title="Test API Request">
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Endpoint</label>
          <select id="endpoint" class="block w-full px-3 py-2 text-sm rounded-lg border-gray-300 shadow-sm">
            <option value="/api/v1/detections">GET /api/v1/detections - List all detections</option>
            <option value="/api/v1/detection/summary">GET /api/v1/detection/summary - Detection summary</option>
            <option value="/api/v1/person/REID_TEST_001">GET /api/v1/person/{reId} - Get person details</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">API Secret</label>
          <input type="password" id="api-secret" placeholder="Enter your API secret"
            class="block w-full px-3 py-2 text-sm rounded-lg border-gray-300 shadow-sm">
          <p class="text-xs text-gray-600 mt-1">Required to make authenticated requests</p>
        </div>

        <div>
          <x-button type="button" variant="primary" onclick="testApi()">
            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Send Test Request
          </x-button>
        </div>
      </div>
    </x-card>

    <!-- Response Display -->
    <x-card title="Response">
      <div id="loading" class="hidden text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
        <p class="text-sm text-gray-600 mt-2">Sending request...</p>
      </div>

      <div id="response-container" class="hidden">
        <div class="mb-3 flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span id="status-badge"></span>
            <span id="response-time" class="text-xs text-gray-500"></span>
          </div>
          <button onclick="copyResponse()" class="text-sm text-blue-600 hover:text-blue-800">
            📋 Copy Response
          </button>
        </div>

        <!-- Headers -->
        <div class="mb-4">
          <h4 class="text-sm font-semibold text-gray-700 mb-2">Response Headers</h4>
          <div id="response-headers" class="text-xs bg-gray-50 p-3 rounded space-y-1"></div>
        </div>

        <!-- Body -->
        <div>
          <h4 class="text-sm font-semibold text-gray-700 mb-2">Response Body</h4>
          <pre id="response-body" class="text-xs bg-gray-900 text-green-400 p-4 rounded overflow-x-auto"></pre>
        </div>
      </div>

      <div id="empty-state" class="text-center py-8 text-gray-500">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <p class="text-sm">No response yet. Send a test request to see results.</p>
      </div>
    </x-card>

    <!-- API Documentation -->
    <x-card title="cURL Example">
      <div class="space-y-3">
        <p class="text-sm text-gray-600">Use this cURL command to test your API from command line:</p>
        <pre id="curl-command" class="text-xs bg-gray-900 text-green-400 p-4 rounded overflow-x-auto">curl -X GET "{{ url('/api/v1/detections') }}" \
  -H "X-API-Key: {{ $apiCredential->api_key }}" \
  -H "X-API-Secret: YOUR_API_SECRET" \
  -H "Accept: application/json"</pre>
        <button onclick="copyCurl()" class="text-sm text-blue-600 hover:text-blue-800">
          📋 Copy cURL Command
        </button>
      </div>
    </x-card>
  </div>

  <script>
    let startTime;

    async function testApi() {
      const endpoint = document.getElementById('endpoint').value;
      const apiKey = document.getElementById('api-key').textContent.trim();
      const apiSecret = document.getElementById('api-secret').value;

      if (!apiSecret) {
        alert('Please enter your API secret');
        return;
      }

      // Show loading
      document.getElementById('empty-state').classList.add('hidden');
      document.getElementById('response-container').classList.add('hidden');
      document.getElementById('loading').classList.remove('hidden');

      startTime = Date.now();

      try {
        // Sanitize headers to ensure only ASCII characters
        const sanitizedApiKey = apiKey.replace(/[^\x20-\x7E]/g, '');
        const sanitizedApiSecret = apiSecret.replace(/[^\x20-\x7E]/g, '');

        const response = await fetch(`{{ url('') }}${endpoint}`, {
          method: 'GET',
          headers: {
            'X-API-Key': sanitizedApiKey,
            'X-API-Secret': sanitizedApiSecret,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });

        const endTime = Date.now();
        const responseTime = endTime - startTime;

        let data;
        try {
          data = await response.json();
        } catch (jsonError) {
          // If JSON parsing fails, get text response
          const textResponse = await response.text();
          data = { error: 'Invalid JSON response', raw: textResponse.substring(0, 500) };
        }

        // Hide loading
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('response-container').classList.remove('hidden');

        // Status badge
        const statusBadge = document.getElementById('status-badge');
        if (response.ok) {
          statusBadge.innerHTML =
            '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">✓ ' + response.status +
            ' ' + response.statusText + '</span>';
        } else {
          statusBadge.innerHTML =
            '<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">✗ ' + response.status + ' ' +
            response.statusText + '</span>';
        }

        // Response time
        document.getElementById('response-time').textContent = `${responseTime}ms`;

        // Headers
        const headersDiv = document.getElementById('response-headers');
        const rateLimit = response.headers.get('X-RateLimit-Limit');
        const rateRemaining = response.headers.get('X-RateLimit-Remaining');
        const rateReset = response.headers.get('X-RateLimit-Reset');
        const contentType = response.headers.get('Content-Type');

        // Sanitize header values
        const sanitizeHeader = (value) => value ? value.replace(/[^\x20-\x7E]/g, '') : '';

        headersDiv.innerHTML = `
          <div><span class="font-semibold">Content-Type:</span> ${sanitizeHeader(contentType)}</div>
          ${rateLimit ? `<div><span class="font-semibold">X-RateLimit-Limit:</span> ${sanitizeHeader(rateLimit)}</div>` : ''}
          ${rateRemaining ? `<div><span class="font-semibold">X-RateLimit-Remaining:</span> ${sanitizeHeader(rateRemaining)}</div>` : ''}
          ${rateReset ? `<div><span class="font-semibold">X-RateLimit-Reset:</span> ${sanitizeHeader(rateReset)}</div>` : ''}
        `;

        // Body
        document.getElementById('response-body').textContent = JSON.stringify(data, null, 2);

        // Update cURL command
        updateCurlCommand(endpoint, apiKey, apiSecret);

      } catch (error) {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('response-container').classList.remove('hidden');

        document.getElementById('status-badge').innerHTML =
          '<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded">✗ Network Error</span>';
        document.getElementById('response-time').textContent = '';
        document.getElementById('response-headers').innerHTML =
        '<div class="text-red-600">Network error occurred</div>';
        document.getElementById('response-body').textContent = error.message;
      }
    }

    function updateCurlCommand(endpoint, apiKey, apiSecret) {
      const curlCmd = `curl -X GET "{{ url('') }}${endpoint}" \\
  -H "X-API-Key: ${apiKey}" \\
  -H "X-API-Secret: ${apiSecret}" \\
  -H "Accept: application/json"`;

      document.getElementById('curl-command').textContent = curlCmd;
    }

    function copyResponse() {
      const responseBody = document.getElementById('response-body').textContent;
      navigator.clipboard.writeText(responseBody).then(() => {
        alert('Response copied to clipboard!');
      });
    }

    function copyCurl() {
      const curlCommand = document.getElementById('curl-command').textContent;
      navigator.clipboard.writeText(curlCommand).then(() => {
        alert('cURL command copied to clipboard!');
      });
    }

    // Update cURL on endpoint change
    document.getElementById('endpoint').addEventListener('change', function() {
      const apiKey = document.getElementById('api-key').textContent.trim();
      updateCurlCommand(this.value, apiKey, 'YOUR_API_SECRET');
    });
  </script>
@endsection
