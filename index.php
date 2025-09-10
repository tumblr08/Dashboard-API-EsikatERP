<?php
$baseUrl = "https://api-dev.v3.esikaterp.id/api/v1/";
$authKey = "7740f9f76920a3e3114234f59a882b1b8655c44bdce68e4694b45e606a970299";

// Daftar endpoint yang akan diambil datanya
$endpoints = [
    'category' => 'Categories',
    'units' => 'Units',
    'items' => 'Items',
    'entity' => 'Entities',
    'departments' => 'Departments',
    'country' => 'Countries',
    'crm' => 'Customer Relationship Management',
    'soc' => 'Sales Order Confirmation',
    'bom' => 'Bill of Materials',
    'productionplan' => 'Production Plan',
    'order' => 'PO Supplier',
    'grn' => 'Goods Receipt Notes',
    'retur' => 'Return Supplier',
    'request' => 'Request Materials',
    'mutation' => 'Transfer Materials to Production',
    'reject' => 'Return Materials to Warehouse',
    'productionprocess' => 'Production Process',
    'subkonout' => 'SubContract Out',
    'subkonin' => 'SubContract In',
    'productionoutput' => 'Production Output',
    'scrapin' => 'Scrap In',
    'scrapout' => 'Scrap Out/Scrap Disposal',
    'scrapoutexternal' => 'Scrap Out External/Scrap Sell',
    'gsn' => 'Goods Send Notes',
    'packinglist' => 'Packing List',
    'assetorder' => 'Asset Orders',
    'asset' => 'Asset Receipt',
    'internalasset' => 'Asset Usage',
    'externalasset' => 'Asset Expenditure',
    'customincoming' => 'Customs Incoming',
    'customoutgoing' => 'Customs Outgoing',
];

// Function untuk melakukan API call
function fetchApiData($url, $authKey) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "X-Auth-Token: $authKey",
            "Accept: application/json"
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'success' => false, 
            'error' => "cURL Error: $error",
            'http_code' => $httpCode
        ];
    }
    
    curl_close($ch);
    
    $data = json_decode($response, true);
    return [
        'success' => true,
        'data' => $data,
        'http_code' => $httpCode,
        'raw_response' => $response
    ];
}

// Function untuk render tabel data dengan action column
function renderDataTable($data, $title = "Data") {
    if (!is_array($data) || empty($data)) {
        return "<div class='alert alert-info'>📭 No data available</div>";
    }

    $html = "<div class='table-container'>";
    $html .= "<h4 class='table-title'>$title</h4>";
    $html .= "<div class='table-scroll'>";
    $html .= "<table class='data-table'>";
    
    // Header tabel dari key array pertama
    $firstItem = is_array($data) ? reset($data) : $data;
    if (is_array($firstItem)) {
        $html .= "<thead><tr>";
        $detailItemExists = false;
        
        foreach (array_keys($firstItem) as $key) {
            // Ganti kolom detail item dengan Action
            if (strtolower($key) === 'detail_item' || strpos(strtolower($key), 'detail') !== false) {
                $html .= "<th>Action</th>";
                $detailItemExists = true;
            } else {
                $html .= "<th>" . ucwords(str_replace('_', ' ', $key)) . "</th>";
            }
        }
        
        // Jika tidak ada kolom detail item, tambahkan kolom action di akhir
        if (!$detailItemExists) {
            $html .= "<th>Action</th>";
        }
        
        $html .= "</tr></thead>";
        
        // Body tabel
        $html .= "<tbody>";
        $rowIndex = 0;
        foreach ($data as $row) {
            $html .= "<tr>";
            $detailData = null;
            
            foreach ($row as $key => $value) {
                // Jika ini kolom detail item, simpan datanya dan buat action button
                if (strtolower($key) === 'detail_item' || strpos(strtolower($key), 'detail') !== false) {
                    $detailData = $value;
                    $html .= "<td class='action-cell'>";
                    $html .= "<button class='action-btn view-detail-btn' onclick='showDetailModal(" . json_encode($value) . ", \"Detail Item\")' title='View Details'>";
                    $html .= "<i class='fas fa-eye'></i>";
                    $html .= "</button>";
                    $html .= "</td>";
                } else {
                    if (is_array($value) || is_object($value)) {
                        $html .= "<td><code>" . json_encode($value, JSON_PRETTY_PRINT) . "</code></td>";
                    } else {
                        $html .= "<td>" . htmlspecialchars($value ?? 'N/A') . "</td>";
                    }
                }
            }
            
            // Jika tidak ada kolom detail item, tambahkan action button kosong atau dengan data keseluruhan row
            if (!$detailItemExists) {
                $html .= "<td class='action-cell'>";
                $html .= "<button class='action-btn view-detail-btn' onclick='showDetailModal(" . json_encode($row) . ", \"Row Details\")' title='View Details'>";
                $html .= "<i class='fas fa-eye'></i>";
                $html .= "</button>";
                $html .= "</td>";
            }
            
            $html .= "</tr>";
            $rowIndex++;
        }
        $html .= "</tbody>";
    } else {
        // Jika data bukan array of arrays, tampilkan sebagai key-value
        $html .= "<tbody>";
        foreach ($data as $key => $value) {
            $html .= "<tr>";
            $html .= "<td><strong>" . ucwords(str_replace('_', ' ', $key)) . "</strong></td>";
            if (is_array($value) || is_object($value)) {
                $html .= "<td><pre>" . json_encode($value, JSON_PRETTY_PRINT) . "</pre></td>";
            } else {
                $html .= "<td>" . htmlspecialchars($value ?? 'N/A') . "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</tbody>";
    }
    
    $html .= "</table>";
    $html .= "</div>";
    $html .= "</div>";
    
    return $html;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>esikatERP API Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .stat-title {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #27ae60;
        }
        
        .content {
            padding: 30px;
        }
        
        .endpoint-section {
            background: white;
            border-radius: 15px;
            margin-bottom: 30px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .endpoint-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .endpoint-title {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .endpoint-url {
            font-family: 'Courier New', monospace;
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .endpoint-content {
            padding: 30px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .alert-info {
            background: #cce7ff;
            color: #004085;
            border-left-color: #007bff;
        }
        
        .table-container {
            margin-top: 20px;
        }
        
        .table-title {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .table-scroll {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .data-table th {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .data-table code {
            background: #f1f3f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        .data-table pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            max-width: 300px;
            overflow-x: auto;
        }
        
        /* Action Button Styles */
        .action-cell {
            text-align: center;
            width: 80px;
            padding: 8px !important;
        }
        
        .action-btn {
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .action-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }
        
        .action-btn:active {
            transform: translateY(0);
        }
        
        .view-detail-btn {
            background: #17a2b8;
        }
        
        .view-detail-btn:hover {
            background: #138496;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border: none;
            border-radius: 15px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            flex-grow: 1;
        }
        
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            padding: 0 10px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .close:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .modal-body {
            padding: 25px;
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .detail-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        
        .detail-item {
            background: white;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .detail-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: capitalize;
        }
        
        .detail-value {
            color: #6c757d;
            font-family: 'Courier New', monospace;
            background: #f1f3f4;
            padding: 8px 12px;
            border-radius: 6px;
            word-break: break-all;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .response-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .info-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            .action-btn {
                width: 30px;
                height: 30px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> esikatERP API Dashboard</h1>
            <p>API Data Analysis & Monitoring</p>
        </div>

        <?php
        $totalEndpoints = count($endpoints);
        $successCount = 0;
        $errorCount = 0;
        $totalRecords = 0;
        $results = [];

        // Fetch data from all endpoints
        foreach ($endpoints as $endpoint => $description) {
            $apiUrl = $baseUrl . $endpoint;
            $result = fetchApiData($apiUrl, $authKey);
            $results[$endpoint] = [
                'description' => $description,
                'url' => $apiUrl,
                'result' => $result
            ];
            
            if ($result['success'] && $result['http_code'] == 200) {
                $successCount++;
                if (isset($result['data']['data']) && is_array($result['data']['data'])) {
                    $totalRecords += count($result['data']['data']);
                }
            } else {
                $errorCount++;
            }
        }
        ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: #3498db;">
                    <i class="fas fa-server"></i>
                </div>
                <div class="stat-title">Total Endpoints</div>
                <div class="stat-value"><?php echo $totalEndpoints; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #27ae60;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-title">Successful</div>
                <div class="stat-value"><?php echo $successCount; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #e74c3c;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-title">Failed</div>
                <div class="stat-value"><?php echo $errorCount; ?></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #f39c12;">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stat-title">Total Records</div>
                <div class="stat-value"><?php echo $totalRecords; ?></div>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <?php foreach ($results as $endpoint => $info): ?>
                <div class="endpoint-section">
                    <div class="endpoint-header">
                        <div>
                            <div class="endpoint-title">
                                <i class="fas fa-plug"></i> <?php echo $info['description']; ?>
                            </div>
                            <div class="endpoint-url"><?php echo $info['url']; ?></div>
                        </div>
                        
                        <?php if ($info['result']['success'] && $info['result']['http_code'] == 200): ?>
                            <div class="status-badge status-success">
                                <i class="fas fa-check"></i> HTTP <?php echo $info['result']['http_code']; ?>
                            </div>
                        <?php else: ?>
                            <div class="status-badge status-error">
                                <i class="fas fa-times"></i> HTTP <?php echo $info['result']['http_code'] ?? 'Error'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="endpoint-content">
                        <?php if (!$info['result']['success']): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Error:</strong> <?php echo $info['result']['error']; ?>
                            </div>
                        
                        <?php elseif ($info['result']['http_code'] != 200): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>HTTP Error:</strong> Request failed with status <?php echo $info['result']['http_code']; ?>
                            </div>
                        
                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Success:</strong> Data retrieved successfully
                            </div>
                            
                            <?php 
                            $responseData = $info['result']['data'];
                            ?>
                            
                            <div class="response-info">
                                <?php if (isset($responseData['status'])): ?>
                                <div class="info-card">
                                    <div class="info-label">API Status</div>
                                    <div class="info-value">
                                        <?php echo $responseData['status'] ? '✅ Success' : '❌ Failed'; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($responseData['message'])): ?>
                                <div class="info-card">
                                    <div class="info-label">Message</div>
                                    <div class="info-value"><?php echo htmlspecialchars($responseData['message']); ?></div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($responseData['data']) && is_array($responseData['data'])): ?>
                                <div class="info-card">
                                    <div class="info-label">Records Found</div>
                                    <div class="info-value"><?php echo count($responseData['data']); ?> records</div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (isset($responseData['data']) && !empty($responseData['data'])): ?>
                                <?php echo renderDataTable($responseData['data'], $info['description'] . ' Data'); ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No detailed data available for this endpoint.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><i class="fas fa-code"></i> esikatERP API Dashboard</p>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Detail Information</h2>
                <span class="close" onclick="closeDetailModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="modalBody" class="detail-container">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to show detail modal
        function showDetailModal(data, title) {
            const modal = document.getElementById('detailModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.textContent = title;
            
            // Clear previous content
            modalBody.innerHTML = '';
            
            if (data && typeof data === 'object') {
                if (Array.isArray(data)) {
                    // If data is an array, show each item
                    data.forEach((item, index) => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'detail-item';
                        itemDiv.innerHTML = `
                            <div class="detail-label">Item ${index + 1}</div>
                            <div class="detail-value">${JSON.stringify(item, null, 2)}</div>
                        `;
                        modalBody.appendChild(itemDiv);
                    });
                } else {
                    // If data is an object, show key-value pairs
                    Object.keys(data).forEach(key => {
                        const detailDiv = document.createElement('div');
                        detailDiv.className = 'detail-item';
                        
                        let value = data[key];
                        if (typeof value === 'object') {
                            value = JSON.stringify(value, null, 2);
                        }
                        
                        detailDiv.innerHTML = `
                            <div class="detail-label">${key.replace(/_/g, ' ')}</div>
                            <div class="detail-value">${value || 'N/A'}</div>
                        `;
                        modalBody.appendChild(detailDiv);
                    });
                }
            } else {
                modalBody.innerHTML = `
                    <div class="detail-item">
                        <div class="detail-label">Value</div>
                        <div class="detail-value">${data || 'No data available'}</div>
                    </div>
                `;
            }
            
            modal.style.display = 'block';
        }
        
        // Function to close detail modal
        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDetailModal();
            }
        });
    </script>
</body>

</html>
