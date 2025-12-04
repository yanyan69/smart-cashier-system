<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$as_of_date = $_GET['as_of_date'] ?? date('Y-m-d');

// Since no historical inventory, fetch current products
$where = "WHERE (user_id = ? OR user_id IS NULL)";
$inventory_query = "SELECT id, product_name, price, stock, unit FROM product $where ORDER BY product_name ASC";
$stmt = $conn->prepare($inventory_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$inventory_result = $stmt->get_result();
$stmt->close();

$store_name = "Techlaro Company"; // Can be made configurable
$location = "Main Store O"; // Example; customize as needed
$valuation_method = "FIFO"; // Assume for all
$owner_name = $_SESSION['username'] ?? "Owner Name";
$tin = "XXX-XXX-XXX-XXX"; // Placeholder

$as_of_formatted = date('F d, Y', strtotime($as_of_date));

$grand_total = 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Inventory Report - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Enhanced report styles similar to receipt */
        .report-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
        }
        .report-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .report-table th {
            background-color: #f4f4f4;
        }
        .report-total {
            text-align: right;
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .report-notes {
            font-size: 12px;
            margin-bottom: 20px;
        }
        .report-declaration {
            text-align: center;
            font-size: 14px;
            margin-top: 40px;
        }
        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        /* Make the table responsive and scrollable on smaller screens */
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
        /* Print-specific styles */
        @media print {
            @page {
                size: landscape;
            }
            .print-button, .sidebar, header, footer, .back-button { display: none; }
            .report-container { margin: 0; padding: 0; border: none; box-shadow: none; }
            body { background: none; }
            .report-table th, .report-table td {
                padding: 4px;
                font-size: 10pt;
            }
            .report-header h1 {
                font-size: 18pt;
            }
            .report-header h2 {
                font-size: 14pt;
            }
            .report-notes {
                font-size: 8pt;
            }
            .report-declaration {
                font-size: 10pt;
            }
        }
    </style>
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Inventory Report</h1>
            </header>

            <section>
                <div class="report-container">
                    <div class="report-header">
                        <h1>ANNEX A</h1>
                        <h2><?php echo strtoupper($store_name); ?> STORE MERCHANDISE ENDING INVENTORY</h2>
                        <p>As of <?php echo $as_of_formatted; ?></p>
                    </div>

                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>PRODUCT / INVENTORY CODE</th>
                                    <th>ITEM DESCRIPTION</th>
                                    <th>LOCATION (Note 1)</th>
                                    <th>INVENTORY VALUATION METHOD</th>
                                    <th>UNIT PRICE (Php)</th>
                                    <th>QUANTITY IN STOCKS</th>
                                    <th>UNIT OF MEASUREMENT (e.g., kilos, grams, liters, etc.)</th>
                                    <th>TOTAL WEIGHT / VOLUME (In weight or volume)</th>
                                    <th>TOTAL COST (Php)</th>
                                    <th>ADDRESS CODE</th>
                                    <th>REMARKS (Note 2)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($product = $inventory_result->fetch_assoc()): ?>
                                    <?php 
                                    $total_cost = $product['price'] * $product['stock'];
                                    $grand_total += $total_cost;
                                    ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                        <td><?php echo $location; ?></td>
                                        <td><?php echo $valuation_method; ?></td>
                                        <td><?php echo number_format($product['price'], 2); ?></td>
                                        <td><?php echo $product['stock']; ?></td>
                                        <td><?php echo htmlspecialchars($product['unit'] ?? ''); ?></td>
                                        <td></td> <!-- Total Weight/Volume: Blank as not available -->
                                        <td><?php echo number_format($total_cost, 2); ?></td>
                                        <td></td> <!-- Address Code: Blank -->
                                        <td></td> <!-- Remarks: Blank -->
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="8" style="text-align: right; font-weight: bold;">T O T A L</td>
                                    <td><?php echo number_format($grand_total, 2); ?></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="report-notes">
                        <p><strong>Note 1</strong></p>
                        <p>a Include all goods whether taxpayer has title thereto or not, provided these goods are actually situated in location/address at the Head Office or Branch or Facilities (with or without sales activity of the taxpayer). Facilities shall include but not limited to place of production, showroom, warehouse, storage place, leased property, etc. Include also goods out on consignment, though not physically present are nonetheless owned by the taxpayer.</p>
                        <p>b Use the following codes:</p>
                        <ul>
                            <li>CH Goods on consignment held by the taxpayer Indicate the name of the consignor in the Remarks column</li>
                            <li>P Parked goods or goods owned by related parties Indicate the name of related party/owner in the Remarks column</li>
                            <li>O Goods owned by the taxpayer</li>
                            <li>CO Goods out on consignment held in the hands of entity other than taxpayer Indicate the name of the entity in the Remarks column</li>
                        </ul>
                        <p><strong>Note 2</strong> Indicate Costing Method applied, e.g., Standard Costing, FIFO, Weighted Average, Specific Identification, etc.</p>
                    </div>

                    <div class="report-declaration">
                        <p>We declare, under the penalties of perjury, that this schedule has been made in good faith, verified by us, and to the best of our knowledge and belief, is true and correct pursuant to the provisions of the National Internal Revenue Code, as amended, and the regulations issued under authority thereof.</p>
                        <p><br><br></p>
                        <p><?php echo strtoupper($owner_name); ?></p>
                        <p>Name and Signature of Authorized</p>
                        <p>Representative</p>
                        <p>TIN : <?php echo $tin; ?></p>
                    </div>
                </div>

                <button class="print-button" onclick="window.print()">Print Report</button>
                <a href="pages/reports.php" class="button back-button" style="display: block; margin: 20px auto; text-align: center; width: fit-content;">Back to Reports</a>
            </section>

            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script src="assets/js/scripts.js"></script>
    <script>
        window.onload = function() {
            const container = document.querySelector('.container');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth' });
            }
        };
    </script>
</body>
</html>

<?php
$conn->close();
?>