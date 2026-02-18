<?php
  // Start the session to access user data.
  session_start();

  // Correctly include files from the 'includes' directory
  require_once('includes/functions.php');
  require_once('includes/db_connect.php'); 

  // --- Page Protection ---
  if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
  }

  $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';

  // ============================================================================
  // == Fetch All Live Data for the Dashboard                                ==
  // ============================================================================

  // --- 1. KPI Card Data ---
  $kpi_query_range = "SELECT SUM(total_price) as total_revenue, COUNT(id) as total_sales FROM sales WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
  $kpi_result_range = $conn->query($kpi_query_range);
  $kpi_data_range = $kpi_result_range->fetch_assoc();
  $total_revenue = $kpi_data_range['total_revenue'] ?? 0;
  $total_sales = $kpi_data_range['total_sales'] ?? 0;

  $distinct_products_query = "SELECT COUNT(id) as distinct_products FROM products";
  $distinct_products_result = $conn->query($distinct_products_query);
  $distinct_products = $distinct_products_result->fetch_assoc()['distinct_products'] ?? 0;

  $total_suppliers_query = "SELECT COUNT(id) as total_suppliers FROM suppliers";
  $total_suppliers_result = $conn->query($total_suppliers_query);
  $total_suppliers = $total_suppliers_result->fetch_assoc()['total_suppliers'] ?? 0;

  // --- 2. Sales Chart Data ---
  $sales_query = "SELECT DATE(sale_date) as date, SUM(total_price) as total_sales 
                  FROM sales 
                  WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                  GROUP BY DATE(sale_date) 
                  ORDER BY DATE(sale_date) ASC";
  $sales_result = $conn->query($sales_query);
  
  $chart_labels = [];
  $chart_data = [];
  
  for ($i = 29; $i >= 0; $i--) {
      $date = date("M d", strtotime("-$i days"));
      $chart_labels[] = $date;
      $chart_data[$date] = 0;
  }
  
  if ($sales_result) {
      while ($row = $sales_result->fetch_assoc()) {
          $date = date("M d", strtotime($row['date']));
          if (isset($chart_data[$date])) {
              $chart_data[$date] = (float)$row['total_sales'];
          }
      }
  }
  $chart_data = array_values($chart_data);

  // --- 3. Recent Sales List ---
  $recent_sales_query = "SELECT s.id, s.total_price, p.product_name, u.name as cashier_name
                         FROM sales s
                         LEFT JOIN products p ON s.product_id = p.id
                         LEFT JOIN users u ON s.user_id = u.id
                         ORDER BY s.sale_date DESC LIMIT 4";
  $recent_sales = $conn->query($recent_sales_query);

  // --- 4. Low Stock Products List (quantity <= 10) ---
  $low_stock_query = "SELECT id, product_name, quantity FROM products WHERE quantity <= 10 ORDER BY quantity ASC LIMIT 3";
  $low_stock_products = $conn->query($low_stock_query);

  // --- 5. Top Selling Products List ---
  $top_selling_query = "SELECT p.product_name, SUM(s.quantity_sold) as total_sold
                        FROM sales s
                        JOIN products p ON s.product_id = p.id
                        WHERE s.sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                        GROUP BY s.product_id
                        ORDER BY total_sold DESC
                        LIMIT 5";
  $top_selling_products = $conn->query($top_selling_query);

  // ============================================================================

  include_once('includes/header.php');
?>

<!-- Main Dashboard Content -->
<div class="dashboard-container">

    <header class="dashboard-header">
        <h1>Dashboard</h1>
        <div class="header-controls">
            <span class="user-welcome">Welcome, <?php echo $username; ?>!</span>
            <select class="date-range-selector">
                <option>Last 30 Days</option>
                <option>Last 7 Days</option>
                <option>This Month</option>
            </select>
        </div>
    </header>

    <section class="kpi-cards">
        <div class="card kpi-card">
            <div class="icon"><i class="fa-solid fa-dollar-sign"></i></div>
            <div class="info">
                <div class="title">Total Revenue</div>
                <div class="value">$<?php echo number_format($total_revenue, 2); ?></div>
            </div>
        </div>
        <div class="card kpi-card">
            <div class="icon"><i class="fa-solid fa-shopping-cart"></i></div>
            <div class="info">
                <div class="title">Total Sales</div>
                <div class="value"><?php echo (int)$total_sales; ?></div>
            </div>
        </div>
        <div class="card kpi-card">
            <div class="icon"><i class="fa-solid fa-box-open"></i></div>
            <div class="info">
                <div class="title">Total Products</div>
                <div class="value"><?php echo (int)$distinct_products; ?></div>
            </div>
        </div>
        <div class="card kpi-card">
            <div class="icon"><i class="fa-solid fa-truck"></i></div>
            <div class="info">
                <div class="title">Suppliers</div>
                <div class="value"><?php echo (int)$total_suppliers; ?></div>
            </div>
        </div>
    </section>

    <section class="main-content-grid">
        <div class="card">
            <h2 class="widget-title">Monthly Sales Performance</h2>
            <div class="chart-wrapper">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="card recent-sales">
            <h2 class="widget-title">Recent Sales</h2>
            <ul class="widget-list recent-sales-list">
                <?php if ($recent_sales && $recent_sales->num_rows > 0): ?>
                    <?php while($sale = $recent_sales->fetch_assoc()): ?>
                        <li>
                            <div class="item-details">
                                <span class="customer-name"><?php echo htmlspecialchars($sale['product_name']); ?></span>
                                <span class="order-id">Sold by <?php echo htmlspecialchars($sale['cashier_name']); ?></span>
                            </div>
                            <span class="amount">$<?php echo number_format($sale['total_price'], 2); ?></span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No recent sales found.</li>
                <?php endif; ?>
            </ul>
            <a href="sales.php" class="view-all-link">View All Sales &rarr;</a>
        </div>
    </section>

    <section class="bottom-widgets">
        <div class="card low-stock">
            <h2 class="widget-title">Low Stock Products</h2>
            <ul class="widget-list low-stock-list">
                <?php if ($low_stock_products && $low_stock_products->num_rows > 0): ?>
                    <?php while($product = $low_stock_products->fetch_assoc()): ?>
                        <li>
                            <span class="item-name"><?php echo htmlspecialchars($product['product_name']); ?></span>
                            <span class="stock-level"><?php echo (int)$product['quantity']; ?> in stock</span>
                            <a href="products.php?action=restock&id=<?php echo (int)$product['id']; ?>" class="restock-btn">Restock</a>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No products are low on stock.</li>
                <?php endif; ?>
            </ul>
            <a href="products.php?filter=low_stock" class="view-all-link">View All &rarr;</a>
        </div>
        <div class="card top-selling">
            <h2 class="widget-title">Top Selling Products</h2>
            <ul class="widget-list top-selling-list">
                <?php if ($top_selling_products && $top_selling_products->num_rows > 0): ?>
                    <?php while($product = $top_selling_products->fetch_assoc()): ?>
                        <li>
                            <span class="item-name"><?php echo htmlspecialchars($product['product_name']); ?></span>
                            <span class="sold-count"><?php echo (int)$product['total_sold']; ?> units sold</span>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No sales in the last 30 days.</li>
                <?php endif; ?>
            </ul>
            <a href="sales_report.php" class="view-all-link">View Full Report &rarr;</a>
        </div>
    </section>

</div>

<style>
    .chart-wrapper { position: relative; height: 400px; width: 100%; }
    .recent-sales-list .item-details { display: flex; flex-direction: column; }
    .recent-sales-list .customer-name { font-weight: 500; }
    .recent-sales-list .order-id { font-size: 0.8rem; color: var(--subtle-text-color); }
    .recent-sales-list .amount { font-weight: 600; color: var(--primary-color); }
    .low-stock-list .item-name { font-weight: 500; }
    .low-stock-list .stock-level { background-color: #ffebee; color: #c62828; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 500; }
    .low-stock-list .restock-btn { background-color: var(--primary-color); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-family: 'Poppins', sans-serif; transition: background-color 0.2s; }
    .top-selling-list .item-name { font-weight: 500; }
    .top-selling-list .sold-count { font-weight: 600; color: #388e3c; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            const labels = <?php echo json_encode($chart_labels); ?>;
            const dataPoints = <?php echo json_encode($chart_data); ?>;

            const salesChart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Sales ($)',
                        data: dataPoints,
                        backgroundColor: 'rgba(74, 144, 226, 0.7)',
                        borderColor: 'rgba(74, 144, 226, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                        hoverBackgroundColor: 'rgba(74, 144, 226, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });
</script>

<?php
  include_once('includes/footer.php');
?>
